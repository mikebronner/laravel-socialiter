<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('social_credentials', function (Blueprint $table) {
                $table->unique(['provider_name', 'provider_id']);
            });
        } catch (\Exception) {
            // Index already exists from a fresh install
        }

        try {
            Schema::table('social_credentials', function (Blueprint $table) {
                $table->index('email');
            });
        } catch (\Exception) {
            // Index already exists from a fresh install
        }
    }

    public function down(): void
    {
        Schema::table('social_credentials', function (Blueprint $table) {
            $table->dropUnique(['provider_name', 'provider_id']);
            $table->dropIndex(['email']);
        });
    }
};
