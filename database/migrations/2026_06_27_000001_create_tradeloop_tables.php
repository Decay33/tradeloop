<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trade_type');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->string('logo_path')->nullable();
            $table->string('google_review_url')->nullable();
            $table->string('facebook_review_url')->nullable();
            $table->decimal('default_tax_rate', 8, 4)->default(0);
            $table->text('default_invoice_terms')->nullable();
            $table->timestamps();
        });

        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner');
            $table->timestamps();
            $table->unique(['business_id', 'user_id']);
            $table->index('user_id');
            $table->index('business_id');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->boolean('sms_consent')->default(false);
            $table->boolean('email_consent')->default(false);
            $table->timestamp('sms_opted_out_at')->nullable();
            $table->timestamp('email_opted_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('business_id');
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->integer('default_price_cents')->default(0);
            $table->integer('default_repeat_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('business_id');
        });

        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('estimate_number');
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->integer('subtotal_cents')->default(0);
            $table->integer('discount_cents')->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->integer('tax_cents')->default(0);
            $table->integer('total_cents')->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['business_id', 'estimate_number']);
            $table->index(['business_id', 'status']);
            $table->index('customer_id');
            $table->index('service_type_id');
        });

        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->integer('unit_price_cents');
            $table->integer('line_total_cents');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('business_id');
            $table->index('estimate_id');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estimate_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('invoice_number');
            $table->string('status')->default('draft');
            $table->integer('subtotal_cents')->default(0);
            $table->integer('discount_cents')->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->integer('tax_cents')->default(0);
            $table->integer('total_cents')->default(0);
            $table->integer('amount_paid_cents')->default(0);
            $table->integer('balance_due_cents')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['business_id', 'invoice_number']);
            $table->index(['business_id', 'status']);
            $table->index('customer_id');
            $table->index('estimate_id');
            $table->index('job_id');
            $table->index('due_date');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estimate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('scheduled');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('followups_scheduled_at')->nullable();
            $table->string('job_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['business_id', 'status']);
            $table->index('customer_id');
            $table->index('estimate_id');
            $table->index('invoice_id');
            $table->index('service_type_id');
            $table->index('scheduled_date');
            $table->index('completed_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('jobs')->nullOnDelete();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->integer('unit_price_cents');
            $table->integer('line_total_cents');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('business_id');
            $table->index('invoice_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->integer('amount_cents');
            $table->string('payment_method');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('business_id');
            $table->index('invoice_id');
            $table->index('payment_date');
        });

        Schema::create('followup_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel');
            $table->string('purpose');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index('business_id');
            $table->index('channel');
            $table->index('purpose');
        });

        Schema::create('followup_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('followup_templates')->cascadeOnDelete();
            $table->string('trigger_event')->default('job_completed');
            $table->integer('delay_amount');
            $table->string('delay_unit');
            $table->string('channel');
            $table->string('purpose');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('business_id');
            $table->index('service_type_id');
            $table->index('template_id');
            $table->index('trigger_event');
            $table->index('is_active');
        });

        Schema::create('followup_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('followup_templates')->nullOnDelete();
            $table->string('channel');
            $table->string('purpose');
            $table->string('status')->default('scheduled');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('skip_reason')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
            $table->index('customer_id');
            $table->index('job_id');
            $table->index('template_id');
            $table->index('channel');
            $table->index('purpose');
            $table->index('scheduled_at');
        });

        Schema::create('message_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('followup_message_id')->constrained('followup_messages')->cascadeOnDelete();
            $table->string('event_type');
            $table->json('event_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('business_id');
            $table->index('followup_message_id');
            $table->index('event_type');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('business_id');
            $table->index('user_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('message_events');
        Schema::dropIfExists('followup_messages');
        Schema::dropIfExists('followup_rules');
        Schema::dropIfExists('followup_templates');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('estimate_items');
        Schema::dropIfExists('estimates');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('business_user');
        Schema::dropIfExists('businesses');
    }
};
