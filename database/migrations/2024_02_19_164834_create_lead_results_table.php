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
        Schema::create('lead_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->integer('status')->default(0);
            $table->string('screen_shot')->nullable();
            $table->foreign('lead_id')->references('id')
                ->on('leads')
                ->onDelete('cascade');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_results');
    }
};
