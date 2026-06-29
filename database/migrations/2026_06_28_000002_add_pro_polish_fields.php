<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        Schema::table('business_user', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('permissions');
            $table->index('is_active');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('quoted_total_cents')->nullable()->after('service_type_id');
            $table->foreignId('assigned_user_id')->nullable()->after('quoted_total_cents')->constrained('users')->nullOnDelete();
            $table->foreignId('started_by_user_id')->nullable()->after('started_at')->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->index('assigned_user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('recorded_by_user_id')->nullable()->after('invoice_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('followup_messages', function (Blueprint $table) {
            $table->foreignId('job_id')->nullable()->change();
            $table->foreignId('estimate_id')->nullable()->after('job_id')->constrained('estimates')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('template_id')->constrained('users')->nullOnDelete();
            $table->boolean('is_manual')->default(false)->after('created_by_user_id');
        });

        Schema::create('invoice_send_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('simulated_sent');
            $table->string('attachment_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index('business_id');
            $table->index('invoice_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_send_events');

        Schema::table('followup_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estimate_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn('is_manual');
            $table->foreignId('job_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recorded_by_user_id');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropConstrainedForeignId('started_by_user_id');
            $table->dropConstrainedForeignId('completed_by_user_id');
            $table->dropColumn('quoted_total_cents');
        });

        Schema::table('business_user', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['permissions', 'is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
