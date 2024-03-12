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
        Schema::dropIfExists('lead_redirects');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('lead_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('link', 255)->nullable();
            $table->text('file')->nullable();
            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')
                ->on('leads')
                ->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
