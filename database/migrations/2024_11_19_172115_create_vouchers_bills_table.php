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
        Schema::create('voucher_bills', function (Blueprint $table) {
            $table->foreignId('bill_id')->constrained();
            $table->unsignedBigInteger('vouchers_id');
             $table->foreign('vouchers_id')->references('id_voucher')->on('vouchers')->onDelete('cascade');

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_bills');
    }
};
