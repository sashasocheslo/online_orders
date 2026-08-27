<x-layout :menu="$menu">
    <div class="container">
        <h2 class="text-white mb-4">Редагувати товар</h2>

        <form
            action="{{ route('menu.products.update', [$menu, $product]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="bg-light p-4 rounded shadow text-dark"
        >
            @csrf
            @method('PUT')

            @include('products._form', [
                'categories' => $categories,
                'product' => $product,
            ])

            <button type="submit" class="btn btn-warning w-100 py-2">
                Оновити товар
            </button>
        </form>
    </div>
</x-layout>
