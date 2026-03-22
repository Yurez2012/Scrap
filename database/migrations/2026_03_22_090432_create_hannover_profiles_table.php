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
        Schema::create('hannover_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('profile_id')->unique()->comment('Talque API profile ID');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website')->nullable();
            $table->json('raw_data')->nullable()->comment('Full JSON response from API');
            $table->boolean('data_fetched')->default(false)->comment('Whether full profile data has been fetched');
            $table->timestamps();

            $table->index('profile_id');
            $table->index('data_fetched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hannover_profiles');
    }
};
