<x-layout :menu="$menu">
    <div class="container py-4">
        <h1 class="text-center mb-4">Оформлення замовлення {{ $menu->name }}</h1>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-5">
                <section class="bg-light text-dark rounded shadow p-4 h-100">
                    <h2 class="h4 mb-3">Ваш кошик</h2>

                    @foreach ($cart->cartProducts as $cartProduct)
                        <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                            <div>
                                <strong>{{ $cartProduct->product->name }}</strong>
                                <div class="text-muted small">
                                    {{ $cartProduct->quantity }} ×
                                    {{ number_format($cartProduct->product->price, 2, ',', ' ') }} ₴
                                </div>
                            </div>
                            <span class="text-nowrap">
                                {{ number_format((float) $cartProduct->product->price * $cartProduct->quantity, 2, ',', ' ') }} ₴
                            </span>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between fs-5 mt-3">
                        <strong>Разом</strong>
                        <strong>{{ number_format($cart->subtotal(), 2, ',', ' ') }} ₴</strong>
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Остаточну суму повторно розрахує сервер під час створення замовлення.
                    </p>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="bg-light text-dark rounded shadow p-4">
                    <h2 class="h4 mb-3">Дані доставки</h2>

                    @error('cart')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form action="{{ route('menu.orders.store', $menu) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <x-label for="phone_number" :required="true">Номер телефону</x-label>
                            <x-text-input
                                name="phone_number"
                                type="tel"
                                placeholder="+380..."
                            />
                        </div>

                        <div class="mb-3">
                            <x-label for="delivery_address" :required="true">Адреса доставки</x-label>
                            <x-text-input
                                name="delivery_address"
                                placeholder="Місто, вулиця, будинок, квартира"
                            />
                        </div>

                        <div class="mb-4">
                            <x-label for="country" :required="true">Країна</x-label>
                            <x-text-input
                                name="country"
                                value="Україна"
                            />
                        </div>

                        <x-button class="w-100">
                            Створити замовлення
                        </x-button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-layout>
