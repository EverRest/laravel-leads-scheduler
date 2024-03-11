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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('protocol', 255)->nullable()->after('country');
            $table->string('country_name', 255)->nullable()->after('country');
            $table->string('ip', 255)->nullable()->after('country');
            $table->string('proxy_external_id', 255)->nullable()->after('country');
            $table->string('host', 255)->nullable()->after('country');
            $table->string('port', 255)->nullable()->after('country');
            $table->string('link', 255)->nullable()->after('country');
            $table->text('file')->nullable()->after('country');
            $table->integer('status')->default(0)->after('country');
            $table->json('data')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'protocol',
                'country_name',
                'ip',
                'proxy_external_id',
                'host',
                'port',
                'username',
                'password',
                'link',
                'file',
                'status',
                'data',
            ]);
        });
    }
};
