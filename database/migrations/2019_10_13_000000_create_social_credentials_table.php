<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSocialCredentialsTable extends Migration
{
    public function up(): void
    {
        Schema::create('social_credentials', function (Blueprint $table) {
            $table->id("id");
            $table->bigInteger("user_id");
            $table->timestamps();

            $table->text("access_token")->nullable();
            $table->string("avatar")->nullable();
            $table->string("email")->nullable();
            $table->string("expires_at")->nullable();
            $table->string("name")->nullable();
            $table->string("nickname")->nullable();
            $table->string("provider_id")->nullable();
            $table->string("provider_name")->nullable();
            $table->text("refresh_token")->nullable();
        });
    }

    public function down(): void
    {
        Schema::drop('social_credentials');
    }
}
