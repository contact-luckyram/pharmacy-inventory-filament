<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('cost_price', 10, 2);
            $table->date('manufactured_at')->nullable();
            $table->date('expires_at')->index();
            $table->timestamps();

            $table->unique(['medicine_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
