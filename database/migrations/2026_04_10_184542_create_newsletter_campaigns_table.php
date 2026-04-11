<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('body');
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sent_to_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->unique()->nullable()->after('ip_address');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->after('unsubscribe_token');
        });

        // Generate tokens for existing subscribers
        foreach (DB::table('newsletter_subscribers')->whereNull('unsubscribe_token')->get() as $sub) {
            DB::table('newsletter_subscribers')->where('id', $sub->id)->update([
                'unsubscribe_token' => Str::random(64),
            ]);
        }

        // Link existing subscribers to clients by email
        $subscribers = DB::table('newsletter_subscribers')->whereNull('client_id')->get();
        foreach ($subscribers as $sub) {
            $client = DB::table('clients')->where('email', $sub->email)->first();
            if ($client) {
                DB::table('newsletter_subscribers')->where('id', $sub->id)->update(['client_id' => $client->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['unsubscribe_token', 'client_id']);
        });

        Schema::dropIfExists('newsletter_campaigns');
    }
};
