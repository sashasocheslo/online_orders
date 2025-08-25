<x-layout>
    <div class="container">
        <h2 class="text-white mb-4">Редагувати продукт</h2>

        <form action="{{ route('menu.products.update', [$menu->id, $product->id]) }}" method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded shadow text-dark">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <x-label for="name" :required="true">Назва</x-label>
                <x-text-input name="name" :value="$product->name" placeholder="Введіть назву" />
            </div>

            <div class="mb-3">
                <x-label for="price" :required="true">Ціна</x-label>
                <x-text-input name="price" type="number" :value="$product->price" placeholder="Введіть ціну" />
            </div>

            <div class="mb-3">
                <x-label for="description">Опис</x-label>
                <x-text-input name="description" :value="$product->description" :textarea="true" placeholder="Опишіть продукт" />
            </div>

            <div class="mb-3">
                <x-label for="category_id">Категорія</x-label>
                <select name="category_id" class="form-select shadow-sm rounded-3">
                    <option value="">Без категорії</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <x-label for="image">Зображення</x-label>
                <input type="file" name="image" class="form-control shadow-sm rounded-3">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" width="150" class="mt-2 rounded shadow">
                @endif
            </div>

            <button type="submit" class="btn btn-warning w-100 py-2">Оновити продукт</button>
        </form>
    </div>
</x-layout>
