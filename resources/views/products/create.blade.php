<x-layout :menu="$menu">
    <div class="container">
        <h2 class="text-white mb-4">Додати товар у {{ $menu->name }}</h2>

        <form
            action="{{ route('menu.products.store', $menu) }}"
            method="POST"
            enctype="multipart/form-data"
            class="bg-light p-4 rounded shadow text-dark"
        >
            @csrf

            @include('products._form', [
                'categories' => $categories,
                'product' => null,
            ])

            <button type="submit" class="btn btn-primary w-100 py-2">
                Зберегти товар
            </button>
        </form>
    </div>
</x-layout>
