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
        Schema::create('people_transforms', function (Blueprint $table) {
            $table->id();
            $table->string('person_uuid');
            $table->string('user_uuid');
            $table->string('address')->nullable();
            $table->text('biography')->nullable();
            $table->string('email')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('jobTitle')->nullable();
            $table->string('mobilePhone')->nullable();
            $table->string('organization')->nullable();
            $table->text('socialNetworks')->nullable();
            $table->text('websiteUrl')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people_transforms');
    }
};
