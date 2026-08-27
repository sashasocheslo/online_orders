<?php

use App\Enums\OrderStatus;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Menu::class)->constrained()->restrictOnDelete();
            $table->string('status')->default(OrderStatus::PendingPayment->value);
            $table->decimal('total', 10, 2);
            $table->string('phone_number', 20);
            $table->string('delivery_address');
            $table->string('country', 100);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['menu_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
