<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Очікує оплати',
            self::Paid => 'Оплачено',
            self::Failed => 'Помилка оплати',
            self::Expired => 'Сесію оплати завершено',
        };
    }
}
