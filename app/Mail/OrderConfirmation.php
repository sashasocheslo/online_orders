<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $phone,
        public string $address,
        public string $country
    )
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('fastfood.team14@gmail.com', 'FastFood'),
            subject: 'Підтвердження замовлення',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
              with: [
                'phone' => $this->phone,
                'address' => $this->address,
                'country' => $this->country,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
