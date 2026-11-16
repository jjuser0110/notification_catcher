<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per upline that shares in a payin. Records how the transaction
     * was split across users and how much each earned into their payin_wallet.
     */
    public function up(): void
    {
        Schema::create('payin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('reload_amount', 18, 2);   // the original payin amount
            $table->decimal('rate', 5, 2);             // this user's rate applied
            $table->decimal('amount', 18, 2);          // amount credited to this user
            $table->timestamp('date_of_record');
            $table->timestamps();

            $table->index(['user_id', 'date_of_record']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payin_logs');
    }
};
