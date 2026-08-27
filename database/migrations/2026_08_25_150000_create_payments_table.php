<?php

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->unique()->constrained()->cascadeOnDelete();
            $table->string('provider')->default('stripe');
            $table->string('status')->default(PaymentStatus::Pending->value);
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('uah');
            $table->string('provider_session_id')->nullable()->unique();
            $table->string('provider_payment_intent_id')->nullable()->unique();
            $table->uuid('idempotency_key')->unique();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
