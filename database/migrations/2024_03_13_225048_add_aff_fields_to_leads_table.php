<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->bigInteger("external_id")->after('country')->nullable();
            $table->string("area_code", 50)->after('country')->nullable();
            $table->string("offer_name", 100)->after('country')->nullable();
            $table->string("offer_url", 100)->after('country')->nullable();
            $table->string("traffic_source",100)->after('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('external_id');
            $table->dropColumn('area_code');
            $table->dropColumn('offer_name');
            $table->dropColumn('offer_url');
            $table->dropColumn('traffic_source');
        });
    }
};
