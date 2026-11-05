<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class DailyActivityLoggingTest extends TestCase
{
    private string $logDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logDirectory = sys_get_temp_dir().'/livro-daily-log-test-'.Str::uuid();
        File::ensureDirectoryExists($this->logDirectory);
        config(['logging.channels.activity.path' => $this->logDirectory.'/activity.log']);
        Log::forgetChannel('activity');
    }

    protected function tearDown(): void
    {
        Log::forgetChannel('activity');
        File::deleteDirectory($this->logDirectory);
        parent::tearDown();
    }

    private function dailyFiles(): array
    {
        return glob($this->logDirectory.'/activity-*.log');
    }

    private function readEntry(string $line): array
    {
        $matched = preg_match('/\] \w+\.(\w+): ([\w.]+) (\{.*\})\s*$/', $line, $matches);
        $this->assertSame(1, $matched, 'O registro deve ser uma linha com contexto JSON válido.');

        return ['level' => $matches[1], 'event' => $matches[2], 'context' => json_decode($matches[3], true, flags: JSON_THROW_ON_ERROR)];
    }

    public function test_real_daily_channel_writes_one_request_record_with_matching_header(): void
    {
        $this->assertSame('daily', config('logging.channels.activity.driver'));
        $response = $this->get('/login?token=private-daily-sentinel')->assertOk();
        $this->assertCount(1, $this->dailyFiles());
        $this->assertSame('activity-'.date('Y-m-d').'.log', basename($this->dailyFiles()[0]));
        $lines = file($this->dailyFiles()[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);
        $entry = $this->readEntry($lines[0]);
        $this->assertSame('INFO', $entry['level']);
        $this->assertSame('http.completed', $entry['event']);
        $this->assertSame($response->headers->get('X-Request-ID'), $entry['context']['request_id']);
        $this->assertSame('GET', $entry['context']['method']);
        $this->assertSame('login', $entry['context']['route']);
        $this->assertSame(200, $entry['context']['status']);
        $this->assertNull($entry['context']['user_id']);
        $this->assertGreaterThanOrEqual(0, $entry['context']['duration_ms']);
        $this->assertStringNotContainsString('private-daily-sentinel', $lines[0]);
    }

    public function test_warning_level_filters_successful_requests_but_keeps_404(): void
    {
        config(['logging.channels.activity.level' => 'warning']);
        $this->get('/login')->assertOk();
        $this->assertCount(0, $this->dailyFiles());
        $this->get('/missing-private-sentinel')->assertNotFound();
        $this->assertCount(1, $this->dailyFiles());
        $lines = file($this->dailyFiles()[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);
        $entry = $this->readEntry($lines[0]);
        $this->assertSame('WARNING', $entry['level']);
        $this->assertSame(404, $entry['context']['status']);
        $this->assertStringNotContainsString('missing-private-sentinel', $lines[0]);
    }

    public function test_rotation_removes_only_excess_daily_files_in_its_own_channel(): void
    {
        config(['logging.channels.activity.days' => 2]);
        foreach (['2001-01-01', '2001-01-02', '2001-01-03'] as $day) {
            File::put($this->logDirectory.'/activity-'.$day.'.log', 'old test record');
        }
        File::put($this->logDirectory.'/other-channel-2001-01-01.log', 'preserve');
        $this->get('/login')->assertOk();
        $this->assertCount(2, $this->dailyFiles());
        $this->assertFileDoesNotExist($this->logDirectory.'/activity-2001-01-01.log');
        $this->assertFileDoesNotExist($this->logDirectory.'/activity-2001-01-02.log');
        $this->assertFileExists($this->logDirectory.'/activity-2001-01-03.log');
        $this->assertFileExists($this->logDirectory.'/activity-'.date('Y-m-d').'.log');
        $this->assertSame('preserve', File::get($this->logDirectory.'/other-channel-2001-01-01.log'));
    }

    public function test_real_file_write_failure_preserves_response_and_emits_safe_fallback(): void
    {
        // Um diretório no lugar do arquivo provoca uma falha real de escrita.
        $blockedPath = $this->logDirectory.'/blocked.log';
        File::ensureDirectoryExists($blockedPath);
        config(['logging.channels.activity' => ['driver' => 'single', 'path' => $blockedPath, 'level' => 'debug']]);
        $fallbackPath = $this->logDirectory.'/fallback.txt';
        $previousErrorLog = ini_set('error_log', $fallbackPath);
        try {
            $response = $this->get('/login?token=private-error-sentinel')->assertOk();
            $this->assertTrue(Str::isUuid($response->headers->get('X-Request-ID')));
            $this->assertFileExists($fallbackPath);
            $fallback = File::get($fallbackPath);
            $this->assertStringContainsString('Activity log unavailable:', $fallback);
            $this->assertStringNotContainsString('private-error-sentinel', $fallback);
            $this->assertDirectoryExists($blockedPath);
        } finally {
            ini_set('error_log', $previousErrorLog);
        }
    }
}
