<div class="modal fade" id="modal-{{ \Illuminate\Support\Str::slug($name) }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">Оберіть розмір для: {{ $name }}</h5>
                <x-button data-bs-dismiss="modal" class="btn-close position-absolute top-0 end-0 m-3"> </x-button>
            </div>

            <div class="modal-body d-flex flex-column align-items-center text-center">
                @foreach ($products as $product)
                    <div class="variant mb-3 w-100 d-flex flex-column align-items-center">
                        <p class="mb-2">
                            <strong>Розмір:</strong> {{ $product->size ?? 'Немає' }}
                        </p>

                        <x-product-card :cart="$cart" :product="$product" type="modal" :$attributes>
                            @can('add', $product)
                            <form action="{{ route('cart_product.store') }}" method="POST">
                                @csrf
                                <x-text-input type="hidden" name="cart_id" :value="$cart?->id" />
                                <x-text-input type="hidden" name="product_id" value="{{ $product->id }}" />
                                <x-button>Додати до кошика</x-button>
                            </form>
                        @else
                            <div class="text-center py-2 fw-bold">
                                Необхідно пройти реєстрацію
                            </div>
                        @endcan
                        </x-product-card>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
