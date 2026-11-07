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
        // 1. Add Only WhatsApp Widget toggle and total redirect count to organization_settings
        Schema::table('organization_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('organization_settings', 'only_whatsapp_widget')) {
                $table->boolean('only_whatsapp_widget')->default(false)->after('is_widget_enabled');
            }
            if (!Schema::hasColumn('organization_settings', 'whatsapp_redirect_count')) {
                $table->unsignedBigInteger('whatsapp_redirect_count')->default(0)->after('total_tokens_used');
            }
        });

        // 2. Add daily WhatsApp redirects counter to organization_chat_usages
        if (Schema::hasTable('organization_chat_usages')) {
            Schema::table('organization_chat_usages', function (Blueprint $table): void {
                if (!Schema::hasColumn('organization_chat_usages', 'whatsapp_redirects_count')) {
                    $table->unsignedInteger('whatsapp_redirects_count')->default(0)->after('queries_count');
                }
            });
        }

        // 3. Add WhatsApp redirection markers to widget_conversations
        if (Schema::hasTable('widget_conversations')) {
            Schema::table('widget_conversations', function (Blueprint $table): void {
                if (!Schema::hasColumn('widget_conversations', 'is_whatsapp_redirected')) {
                    $table->boolean('is_whatsapp_redirected')->default(false)->after('status');
                }
                if (!Schema::hasColumn('widget_conversations', 'whatsapp_redirected_at')) {
                    $table->timestamp('whatsapp_redirected_at')->nullable()->after('is_whatsapp_redirected');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('organization_settings', 'only_whatsapp_widget')) {
                $table->dropColumn('only_whatsapp_widget');
            }
            if (Schema::hasColumn('organization_settings', 'whatsapp_redirect_count')) {
                $table->dropColumn('whatsapp_redirect_count');
            }
        });

        if (Schema::hasTable('organization_chat_usages')) {
            Schema::table('organization_chat_usages', function (Blueprint $table): void {
                if (Schema::hasColumn('organization_chat_usages', 'whatsapp_redirects_count')) {
                    $table->dropColumn('whatsapp_redirects_count');
                }
            });
        }

        if (Schema::hasTable('widget_conversations')) {
            Schema::table('widget_conversations', function (Blueprint $table): void {
                if (Schema::hasColumn('widget_conversations', 'is_whatsapp_redirected')) {
                    $table->dropColumn(['is_whatsapp_redirected', 'whatsapp_redirected_at']);
                }
            });
        }
    }
};
