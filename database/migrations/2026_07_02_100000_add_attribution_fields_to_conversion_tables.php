<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->foreignId('landing_post_id')->nullable()->after('property_id')->constrained('posts')->nullOnDelete();
            $table->string('landing_label')->nullable()->after('landing_post_id');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignId('landing_post_id')->nullable()->after('referrer')->constrained('posts')->nullOnDelete();
            $table->string('landing_label')->nullable()->after('landing_post_id');
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->foreignId('landing_post_id')->nullable()->after('source')->constrained('posts')->nullOnDelete();
            $table->string('landing_label')->nullable()->after('landing_post_id');
            $table->string('utm_source')->nullable()->after('landing_label');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('referrer')->nullable()->after('utm_campaign');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landing_post_id');
            $table->dropColumn('landing_label');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landing_post_id');
            $table->dropColumn('landing_label');
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landing_post_id');
            $table->dropColumn(['landing_label', 'utm_source', 'utm_medium', 'utm_campaign', 'referrer']);
        });
    }
};
