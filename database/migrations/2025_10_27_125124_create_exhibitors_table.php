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
        Schema::create('exhibitors', function (Blueprint $table) {
            $table->id();
            $table->string('exhibitor_id');
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_fax')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_facebook')->nullable();
            $table->string('company_instagram')->nullable();
            $table->string('company_linkedin')->nullable();
            $table->string('company_youtube')->nullable();

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('address3')->nullable();
            $table->string('city')->nullable();
            $table->string('postal')->nullable();
            $table->string('country')->nullable();

            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_email')->nullable();
            $table->string('invoice_address1')->nullable();
            $table->string('invoice_address2')->nullable();
            $table->string('invoice_iso_code', 5)->nullable();
            $table->string('invoice_postal')->nullable();

            $table->string('stand_id')->nullable();
            $table->string('stand_nr')->nullable();
            $table->string('stand_link')->nullable();

            $table->string('project_id')->nullable();
            $table->string('project_name')->nullable();
            $table->string('project_name_en')->nullable();
            $table->string('project_name_sv')->nullable();

            $table->text('fair_catalog_text')->nullable();
            $table->text('fair_catalogue_text_en')->nullable();
            $table->text('fair_catalogue_text_sv')->nullable();

            $table->string('meeting_reservation_link')->nullable();
            $table->string('organisation_number')->nullable();
            $table->string('url')->nullable();

            $table->json('products')->nullable();
            $table->json('themes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibitors');
    }
};
