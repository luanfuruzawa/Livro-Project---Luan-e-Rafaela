<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    { 
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade'); 
            $table->foreignId('livro_id')->constrained('livros')->onDelete('cascade');
            $table->integer('quantidade');
            $table->decimal('valor_total', 10, 2); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};