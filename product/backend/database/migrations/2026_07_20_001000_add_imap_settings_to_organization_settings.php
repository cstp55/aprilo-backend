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
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('mail_imap_host')->nullable();
            $table->integer('mail_imap_port')->nullable();
            $table->string('mail_imap_username')->nullable();
            $table->string('mail_imap_password')->nullable();
            $table->string('mail_imap_encryption')->default('ssl');
            $table->boolean('mail_imap_connected')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_imap_host',
                'mail_imap_port',
                'mail_imap_username',
                'mail_imap_password',
                'mail_imap_encryption',
                'mail_imap_connected',
            ]);
        });
    }
};
