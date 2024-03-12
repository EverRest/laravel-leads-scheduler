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
        Schema::dropIfExists('lead_proxies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('lead_proxies', function (Blueprint $table) {
            $table->id();
            $table->string('protocol', 255);
            $table->string('country', 50);
            $table->string('ip', 100);
            $table->string('external_id', 100)->nullable();
            $table->string('host', 100);
            $table->string('port', 255)->nullable();
            $table->string('username', 50);
            $table->string('password', 255)->nullable();
            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')
                ->on('leads')
                ->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
