<x-layout :menu="$order->menu">
    <div class="container py-4">
        <div class="bg-light text-dark rounded shadow p-4 mx-auto" style="max-width: 900px;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="h2 mb-1">Замовлення №{{ $order->id }}</h1>
                    <div class="text-muted">
                        {{ $order->menu->name }} · {{ $order->created_at->format('d.m.Y H:i') }}
                    </div>
                </div>

                <span @class([
                    'badge fs-6',
                    'text-bg-success' => $order->status === \App\Enums\OrderStatus::Paid,
                    'text-bg-warning' => $order->status !== \App\Enums\OrderStatus::Paid,
                ])>
                    {{ $order->status->label() }}
                </span>
            </div>

            <h2 class="h4">Товари</h2>
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th class="text-end">Ціна</th>
                            <th class="text-center">Кількість</th>
                            <th class="text-end">Сума</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2, ',', ' ') }} ₴</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->lineTotal(), 2, ',', ' ') }} ₴</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Разом</th>
                            <th class="text-end">{{ number_format($order->total, 2, ',', ' ') }} ₴</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <h2 class="h4">Доставка</h2>
                    <dl class="mb-0">
                        <dt>Телефон</dt>
                        <dd>{{ $order->phone_number }}</dd>
                        <dt>Адреса</dt>
                        <dd>{{ $order->delivery_address }}</dd>
                        <dt>Країна</dt>
                        <dd>{{ $order->country }}</dd>
                    </dl>
                </div>

                <div class="col-md-6">
                    <h2 class="h4">Історія статусів</h2>
                    <ul class="list-group">
                        @foreach ($order->statusHistory as $history)
                            <li class="list-group-item d-flex justify-content-between gap-2">
                                <span>{{ $history->status->label() }}</span>
                                <small class="text-muted">{{ $history->created_at->format('d.m.Y H:i') }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-between flex-wrap gap-2 mt-4">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        До історії замовлень
                    </a>

                    @can('delete', $order)
                        <form
                            action="{{ route('orders.destroy', $order) }}"
                            method="POST"
                            onsubmit="return confirm('Видалити замовлення №{{ $order->id }}? Цю дію неможливо скасувати.')"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                                Видалити замовлення
                            </button>
                        </form>
                    @endcan
                </div>

                <div class="text-end">
                    @if ($order->payment !== null)
                        <div class="text-muted small mb-2">
                            Стан платежу: {{ $order->payment->status->label() }}
                        </div>
                    @endif

                    @can('pay', $order)
                        @if ($order->status === \App\Enums\OrderStatus::PendingPayment)
                            <form action="{{ route('orders.payment.store', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Оплатити {{ number_format($order->total, 2, ',', ' ') }} ₴ через Stripe
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-layout>
