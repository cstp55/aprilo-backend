<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (! Schema::hasColumn('organizations', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            if (! Schema::hasColumn('organizations', 'country')) {
                $table->string('country')->nullable()->after('website');
            }
            if (! Schema::hasColumn('organizations', 'industry')) {
                $table->string('industry')->nullable()->after('country');
            }
            if (! Schema::hasColumn('organizations', 'team_size')) {
                $table->string('team_size')->nullable()->after('industry');
            }
            if (! Schema::hasColumn('organizations', 'timezone')) {
                $table->string('timezone')->nullable()->after('team_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            $cols = ['email', 'website', 'country', 'industry', 'team_size', 'timezone'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('organizations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};