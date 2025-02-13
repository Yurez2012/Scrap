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
        Schema::table('people', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('last_name');
            $table->text('summary')->nullable()->after('headline');
            $table->string('job_title')->nullable()->after('summary');
            $table->string('company_name')->nullable()->after('job_title');
            $table->string('location')->nullable()->after('company_name');
            $table->string('picture_url')->nullable()->after('location');
            $table->string('type_key_translation')->nullable()->after('picture_url');
            $table->text('company_description')->nullable()->after('type_key_translation');
            $table->string('company_website')->nullable()->after('company_description');
            $table->string('current_role')->nullable()->after('company_website');
            $table->string('hardwaresoftware_investing')->nullable()->after('current_role');
            $table->string('industry')->nullable()->after('hardwaresoftware_investing');
            $table->string('investment_region')->nullable()->after('industry');
            $table->string('investor_type')->nullable()->after('investment_region');
            $table->string('linkedin_profile')->nullable()->after('investor_type');
            $table->text('quick_introduction_about_yourself')->nullable()->after('linkedin_profile');
            $table->string('typical_ticket_size')->nullable()->after('quick_introduction_about_yourself');
            $table->string('what_are_you_looking_for')->nullable()->after('typical_ticket_size');
            $table->string('what_is_your_investment_thesis')->nullable()->after('what_are_you_looking_for');
            $table->string('will_you_join_the_investor_day')->nullable()->after('what_is_your_investment_thesis');
            $table->string('investment_stage')->nullable()->after('will_you_join_the_investor_day');
            $table->text('topics_of_interest')->nullable()->after('investment_stage');
            $table->string('attendee_type')->nullable()->after('topics_of_interest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('headline');
            $table->dropColumn('summary');
            $table->dropColumn('job_title');
            $table->dropColumn('company_name');
            $table->dropColumn('location');
            $table->dropColumn('picture_url');
            $table->dropColumn('type_key_translation');
            $table->dropColumn('company_description');
            $table->dropColumn('company_website');
            $table->dropColumn('current_role');
            $table->dropColumn('hardwaresoftware_investing');
            $table->dropColumn('industry');
            $table->dropColumn('investment_region');
            $table->dropColumn('investor_type');
            $table->dropColumn('linkedin_profile');
            $table->dropColumn('quick_introduction_about_yourself');
            $table->dropColumn('typical_ticket_size');
            $table->dropColumn('what_are_you_looking_for');
            $table->dropColumn('what_is_your_investment_thesis');
            $table->dropColumn('will_you_join_the_investor_day');
            $table->dropColumn('investment_stage');
            $table->dropColumn('topics_of_interest');
            $table->dropColumn('attendee_type');
        });
    }
};
