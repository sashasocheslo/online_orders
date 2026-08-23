<x-layout :menu="$menu">
    <div class="container py-4">
        <h2 class="mb-4 text-center text-white">Кошик {{ $menu->name }}</h2>

        @if ($cart)
            <section class="bg-light text-dark rounded shadow p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h3 class="mb-0">{{ $menu->name }}</h3>
                    <strong class="fs-5">
                        Разом: {{ number_format($cart->subtotal(), 2, ',', ' ') }} ₴
                    </strong>
                </div>

                <div class="row g-3">
                    @foreach ($cart->cartProducts as $cartProduct)
                        <div class="col-lg-4 col-md-6 col-12 d-flex">
                            <x-product-card :product="$cartProduct->product" type="cart" class="w-100">
                                <div class="quantity-selector d-flex align-items-center justify-content-center gap-2 mt-3">
                                    @if ($cartProduct->quantity > 1)
                                        <form action="{{ route('cart_product.update', $cartProduct) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $cartProduct->quantity - 1 }}">
                                            <x-button class="btn-quantity-minus" aria-label="Зменшити кількість">−</x-button>
                                        </form>
                                    @else
                                        <form action="{{ route('cart_product.destroy', $cartProduct) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <x-button class="btn-quantity-minus" aria-label="Видалити останню одиницю">−</x-button>
                                        </form>
                                    @endif

                                    <span class="quantity-value fw-bold">{{ $cartProduct->quantity }}</span>

                                    <form action="{{ route('cart_product.update', $cartProduct) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $cartProduct->quantity + 1 }}">
                                        <x-button
                                            class="btn-quantity-plus"
                                            aria-label="Збільшити кількість"
                                            :disabled="$cartProduct->quantity >= 99"
                                        >+</x-button>
                                    </form>
                                </div>

                                <form
                                    action="{{ route('cart_product.destroy', $cartProduct) }}"
                                    method="POST"
                                    class="mt-3"
                                    onsubmit="return confirm('Видалити товар із кошика?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        Видалити позицію
                                    </button>
                                </form>
                            </x-product-card>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <a href="{{ route('menu.show', $menu) }}" class="btn btn-outline-secondary">
                        Повернутися до меню
                    </a>

                    <a href="{{ route('menu.orders.create', $menu) }}">
                        <x-button class="btn-buy">
                            Оформити на {{ number_format($cart->subtotal(), 2, ',', ' ') }} ₴
                        </x-button>
                    </a>
                </div>
            </section>
        @else
            <div class="bg-light text-dark rounded shadow p-4 text-center">
                <p class="mb-3">Кошик {{ $menu->name }} порожній.</p>
                <a href="{{ route('menu.show', $menu) }}" class="btn btn-primary">
                    Перейти до меню {{ $menu->name }}
                </a>
            </div>
        @endif
    </div>
</x-layout>
