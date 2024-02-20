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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('phone', 50)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('offer_url', 255);
            $table->string('country', 10);
            $table->string('ip', 50);
            $table->unsignedBigInteger('partner_id');
            $table->foreign('partner_id')->references('id')
                ->on('partners')
                ->onDelete('cascade');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('scheduled_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
