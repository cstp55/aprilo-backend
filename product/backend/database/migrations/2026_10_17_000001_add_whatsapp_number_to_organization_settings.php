<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_settings', 'whatsapp_number')) {
                $table->string('whatsapp_number', 100)->nullable()->after('whatsapp_sender_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            if (Schema::hasColumn('organization_settings', 'whatsapp_number')) {
                $table->dropColumn('whatsapp_number');
            }
        });
    }
};
