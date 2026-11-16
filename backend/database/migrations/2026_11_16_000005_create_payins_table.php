<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();        // merchant the payin belongs to
            $table->foreignId('bank_setting_id')->nullable()->constrained('bank_settings')->nullOnDelete();
            $table->string('transaction_no')->unique();
            $table->decimal('amount', 18, 2);
            $table->string('reference_no')->nullable();
            $table->string('player')->nullable();
            $table->string('callback_url', 1000)->nullable();
            $table->string('redirect_url', 1000)->nullable();
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
            $table->string('status', 20)->default('pending');   // pending|success|failed|cancelled
            $table->decimal('rate', 5, 2)->nullable();
            $table->decimal('nett_amount', 18, 2)->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->longText('callback_response')->nullable();
            $table->string('notify_status', 20)->nullable();
            $table->unsignedInteger('callback_attempt')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('manual_update_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payins');
    }
};
