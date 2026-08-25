<x-layout>
    <div class="container py-4">
        <h1 class="text-center mb-4">Історія замовлень</h1>

        <div class="row g-3 justify-content-center">
            @forelse ($orders as $order)
                <div class="col-lg-8">
                    <a
                        href="{{ route('orders.show', $order) }}"
                        class="text-decoration-none"
                    >
                        <article class="bg-light text-dark rounded shadow p-4">
                            <div class="d-flex justify-content-between flex-wrap gap-3">
                                <div>
                                    <h2 class="h4 mb-1">Замовлення №{{ $order->id }}</h2>
                                    <div>{{ $order->menu->name }}</div>
                                    @if (auth()->user()->isAdmin())
                                        <div class="text-muted small">Користувач: {{ $order->user->email }}</div>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <span @class([
                                        'badge',
                                        'text-bg-success' => $order->status === \App\Enums\OrderStatus::Paid,
                                        'text-bg-warning' => $order->status !== \App\Enums\OrderStatus::Paid,
                                    ])>
                                        {{ $order->status->label() }}
                                    </span>
                                    <div class="fw-bold mt-2">
                                        {{ number_format($order->total, 2, ',', ' ') }} ₴
                                    </div>
                                    <div class="text-muted small">
                                        {{ $order->items_count }} позицій · {{ $order->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    </a>
                </div>
            @empty
                <div class="col-lg-8">
                    <div class="bg-light text-dark rounded shadow p-4 text-center">
                        У вас ще немає замовлень.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
