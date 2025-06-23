<x-layout>
    <div class="d-flex justify-content-center align-items-start mt-4">
        <x-card type="cart">
            <h5 class="mb-2 fw-bold text-center">Ваше замовлення</h5>

            @forelse ($cart_products as $cart_product)
                <div class="w-100 d-flex flex-column product-card-wrapper mb-3">
                    <x-product-card :product="$cart_product->product" type="cart">
                        <div class="quantity-selector d-flex align-items-center justify-content-center gap-2 mt-auto">
                            <div class="d-flex align-items-center gap-2">
                                <form action="{{ route('cart_product.destroy', $cart_product) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <x-button class="btn-quantity-minus">−</x-button>
                                </form>

                                <span class="quantity-value">{{ $cart_product->quantity }}</span>

                                <form action="{{ route('cart_product.store') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $cart_product->product_id }}">
                                    <input type="hidden" name="cart_id" value="{{ $cart_product->cart_id }}">
                                    <x-button class="btn-quantity-plus">+</x-button>
                                </form>
                            </div>
                        </div>
                    </x-product-card>
                </div>
            @empty
                <p class="text-center">Кошик пустий.</p>
            @endforelse

            <div class="d-flex justify-content-center mt-3">
                <a href="{{ route('order.create', [
                    'amount' => $cart_products->sum(fn($cp) => $cp->product->price * $cp->quantity),
                ]) }}" class="w-100">
                    <x-button class="w-100">
                        Купити 
                        {{ number_format($cart_products->sum(fn($cp) => $cp->product->price * $cp->quantity), 2, ',', ' ') }} ₴
                    </x-button>
                </a>
            </div>
        </x-card>
    </div>
</x-layout>
