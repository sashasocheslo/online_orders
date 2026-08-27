<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Preparing = 'preparing';
    case Delivering = 'delivering';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Очікує оплати',
            self::Paid => 'Оплачено',
            self::Preparing => 'Готується',
            self::Delivering => 'Доставляється',
            self::Completed => 'Завершено',
            self::Cancelled => 'Скасовано',
        };
    }
}
