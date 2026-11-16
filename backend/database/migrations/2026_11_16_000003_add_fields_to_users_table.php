<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->after('password'); // superadmin|admin|staff|customer|merchant
            $table->foreignId('parent_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('parent_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Rates apply to customer & merchant only.
            $table->decimal('payin_rate', 5, 2)->nullable()->after('last_login_at');
            $table->decimal('payout_rate', 5, 2)->nullable()->after('payin_rate');

            // Wallets / quota.
            $table->decimal('payin_wallet', 18, 2)->default(0)->after('payout_rate');
            $table->decimal('payout_wallet', 18, 2)->default(0)->after('payin_wallet');
            $table->decimal('all_wallet', 18, 2)->default(0)->after('payout_wallet');
            $table->decimal('quota', 18, 2)->default(0)->after('all_wallet');

            // API credentials — merchant only, auto-generated on creation.
            $table->string('api_token', 80)->nullable()->unique()->after('quota');
            $table->string('api_key', 80)->nullable()->unique()->after('api_token');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'role', 'is_active', 'last_login_at', 'payin_rate', 'payout_rate',
                'payin_wallet', 'payout_wallet', 'all_wallet', 'quota', 'api_token', 'api_key',
            ]);
        });
    }
};
