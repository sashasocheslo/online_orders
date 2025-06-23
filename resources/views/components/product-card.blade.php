<x-card :type="$attributes->get('type') ?? 'product'">
    <div class="d-flex flex-column {{ $attributes->get('type') === 'cart' ? 'align-items-center' : 'text-center' }} w-100">
        <x-photo-product :$product />
        <h5 class="fw-bold text-dark mt-2 mb-1">{{ $product->name }}</h5>
        <p class="text-secondary mb-1">{{ $product->description }}</p>
        <div class="fw-normal text-dark fs-5 text-nowrap">
            {{ number_format($product->price, 2, ',', ' ') }} ₴
        </div>
        <div class="text-muted small mb-2">
            Категорія: {{ $product->category->name }}
        </div>

        <div class="mt-2">
            {{ $slot }}
        </div>
    </div>
</x-card>
