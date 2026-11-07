<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titular', 100);
            $table->string('bandeira', 30);
            $table->string('ultimos_quatro', 4);
            $table->unsignedTinyInteger('validade_mes');
            $table->unsignedSmallInteger('validade_ano');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoes');
    }
};
