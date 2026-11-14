<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['notification', 'sms']);
            $table->string('app_package')->nullable();     // e.g. com.whatsapp (notifications)
            $table->string('app_name')->nullable();        // e.g. WhatsApp
            $table->string('sender')->nullable();          // SMS phone number
            $table->string('title', 500)->nullable();
            $table->text('body')->nullable();
            $table->timestamp('received_at')->index();     // time on the phone
            $table->string('hash', 64);                    // dedupe key so retries don't duplicate
            $table->timestamps();

            $table->unique(['device_id', 'hash']);
            $table->index(['type', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
