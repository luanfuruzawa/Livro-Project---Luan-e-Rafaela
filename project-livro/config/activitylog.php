<?php

return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 365,
    'default_log_name' => 'sistema',
    'default_auth_driver' => null,
    'subject_returns_soft_deleted_models' => true,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'table_name' => 'activity_log',
    'database_connection' => null,
];
