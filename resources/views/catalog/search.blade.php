<x-layout>
    <div class="container py-3">
        <x-card class="mb-3">
            <h2 class="fw-bold text-dark">Пошук по всьому каталогу</h2>
            <p class="text-secondary mb-3">
                Знайдіть реальні товари одразу в усіх ресторанах.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('catalog.search') }}" method="GET" class="row g-2">
                <div class="col-12 col-lg-4">
                    <label for="query" class="form-label text-dark">Що шукаємо?</label>
                    <input
                        id="query"
                        name="query"
                        type="search"
                        value="{{ request('query') }}"
                        class="form-control"
                        maxlength="120"
                        placeholder="Наприклад, бургер або десерт"
                    >
                </div>

                <div class="col-12 col-md-6 col-lg-2">
                    <label for="menu_id" class="form-label text-dark">Ресторан</label>
                    <select id="menu_id" name="menu_id" class="form-select">
                        <option value="">Усі</option>
                        @foreach ($menus as $menu)
                            <option
                                value="{{ $menu->id }}"
                                @selected((string) request('menu_id') === (string) $menu->id)
                            >
                                {{ $menu->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-2">
                    <label for="category_id" class="form-label text-dark">Категорія</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Усі</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) request('category_id') === (string) $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="min_price" class="form-label text-dark">Ціна від</label>
                    <input
                        id="min_price"
                        name="min_price"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ request('min_price') }}"
                        class="form-control"
                    >
                </div>

                <div class="col-6 col-lg-2">
                    <label for="max_price" class="form-label text-dark">Ціна до</label>
                    <input
                        id="max_price"
                        name="max_price"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ request('max_price') }}"
                        class="form-control"
                    >
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label for="sort" class="form-label text-dark">Сортування</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="price_asc" @selected(request('sort', 'price_asc') === 'price_asc')>
                            Спочатку дешевші
                        </option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>
                            Спочатку дорожчі
                        </option>
                        <option value="name" @selected(request('sort') === 'name')>
                            За назвою
                        </option>
                    </select>
                </div>

                <div class="col-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        Знайти
                    </button>
                    <a href="{{ route('catalog.search') }}" class="btn btn-outline-secondary">
                        Скинути
                    </a>
                </div>
            </form>
        </x-card>

        <x-card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-dark mb-0">Результати</h3>
                <span class="text-secondary">
                    Знайдено: {{ $products->total() }}
                </span>
            </div>

            <div class="row g-3">
                @forelse ($products as $product)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h4 class="card-title text-dark fs-5">
                                    {{ $product->name }}
                                </h4>

                                <p class="text-secondary mb-2">
                                    {{ $product->description ?: 'Опис поки відсутній.' }}
                                </p>

                                @if ($product->size)
                                    <p class="text-secondary mb-1">
                                        Розмір: {{ $product->size }}
                                    </p>
                                @endif

                                <p class="text-secondary mb-1">
                                    Ресторан: {{ $product->menu->name }}
                                </p>
                                <p class="text-secondary mb-3">
                                    Категорія: {{ $product->category->name }}
                                </p>

                                <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                                    <strong class="text-dark fs-5">
                                        {{ number_format((float) $product->price, 2, ',', ' ') }} ₴
                                    </strong>
                                    <a
                                        href="{{ route('menu.show', $product->menu) }}"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        Відкрити меню
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            За вказаними умовами товарів не знайдено. Спробуйте змінити фільтри.
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($products->hasPages())
                <nav class="d-flex justify-content-between align-items-center mt-4"
                     aria-label="Сторінки результатів пошуку">
                    @if ($products->onFirstPage())
                        <span class="btn btn-outline-secondary disabled">Назад</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}"
                           class="btn btn-outline-primary">
                            Назад
                        </a>
                    @endif

                    <span class="text-secondary">
                        Сторінка {{ $products->currentPage() }} з {{ $products->lastPage() }}
                    </span>

                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}"
                           class="btn btn-outline-primary">
                            Далі
                        </a>
                    @else
                        <span class="btn btn-outline-secondary disabled">Далі</span>
                    @endif
                </nav>
            @endif
        </x-card>
    </div>
</x-layout>
