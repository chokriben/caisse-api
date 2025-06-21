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
       Schema::create('cash_closures', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->decimal('total_sales', 10, 2);      // Total des ventes
    $table->decimal('total_received', 10, 2);   // Montant encaissé
    $table->decimal('total_change', 10, 2);     // Monnaie rendue
    $table->decimal('real_cash', 10, 2);        // Montant réellement dans la caisse (saisi manuellement)
    $table->decimal('difference', 10, 2);       // Écart éventuel
    $table->unsignedBigInteger('user_id')->nullable(); // Qui a clôturé
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closures');
    }
};
