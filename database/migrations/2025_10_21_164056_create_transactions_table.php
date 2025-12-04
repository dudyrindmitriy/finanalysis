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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['income', 'expense']);
            $table->date('date');
            $table->time('time')->nullable();
            $table->text('description');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('bank_name');
            $table->string('mcc_code')->nullable();
            $table->string('bank_category')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_hash')->unique();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
