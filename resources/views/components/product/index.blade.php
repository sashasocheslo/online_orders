<div class="row g-3 d-flex justify-content-start">
    @foreach ($products->groupBy('name') as $name => $product)
        <div class="col-lg-4 col-md-6 col-12 d-flex">
            <x-product-card :product="$product->first()" class="w-100 h-100">
                <x-button :name="$name">
                    Обрати
                </x-button>
            </x-product-card>
        </div>
        <x-product-modal :$name :products="$product" :cart="$cart" />
    @endforeach
</div>
