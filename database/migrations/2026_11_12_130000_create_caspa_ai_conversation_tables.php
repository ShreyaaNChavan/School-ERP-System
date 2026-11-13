<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('caspa_ai_conversations') &&
            !Schema::hasColumn('caspa_ai_conversations', 'screen')
        ) {
            Schema::table('caspa_ai_conversations', function (Blueprint $table) {
                $table->string('screen', 60)
                    ->default('chat')
                    ->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('caspa_ai_conversations') &&
            Schema::hasColumn('caspa_ai_conversations', 'screen')
        ) {
            Schema::table('caspa_ai_conversations', function (Blueprint $table) {
                $table->dropColumn('screen');
            });
        }
    }
};
