<x-layout>
    <x-card>
        <h3>Додати продукт у меню {{ $menu->name }}</h3>

        <form action="{{ route('menu.products.store', $menu) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-2">
                <x-text-input type="text" name="name" placeholder="Назва продукту" required />
            </div>

            <div class="mb-2">
                <x-text-input type="number" name="price" placeholder="Ціна" required />
            </div>

            <div class="mb-2">
                <select name="category_id" class="form-select">
                    <option value="">Без категорії</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-2">
                <x-text-input type="file" name="image" accept="image/*" required />
            </div>

            <x-button>Зберегти</x-button>
        </form>
    </x-card>
</x-layout>
