<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_settings', function (Blueprint $table) {
            $table->id();
            // Payin rates (percent of the player's amount credited). Band: max..default.
            $table->decimal('payin_cost_rate', 5, 2)->default(100);
            $table->decimal('payin_default_rate', 5, 2)->default(98);
            $table->decimal('payin_max_rate', 5, 2)->default(95);
            // Payout rates (percent fee). Band: default..max.
            $table->decimal('payout_cost_rate', 5, 2)->default(0);
            $table->decimal('payout_default_rate', 5, 2)->default(2);
            $table->decimal('payout_max_rate', 5, 2)->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_settings');
    }
};
