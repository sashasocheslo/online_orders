<x-layout>
    <div class="container-fluid mt-0">

        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-8">
                <x-card class="mb-2">
                    <h4 class="fw-bold text-dark fs-1 font-mcdonalds">
                        {{ $menu->name }}
                    </h4>
                    <p class="fs-5 text-secondary fst-italic mt-1">
                        Оберіть ваше меню
                    </p>
                </x-card>

                <x-card class="mb-3">
                    <form id="category-form" action="{{ route('menu.show', $menu) }}" method="GET" class="d-flex w-100 align-items-center">
                        <div class="position-relative flex-grow-1 me-2">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary"></i>
                            <x-text-input name="search" value="{{ request('search') }}" placeholder="Шукати в {!! $menu->name !!}" form-id="category-form" class="ps-5" />
                        </div>
                        <x-button>
                            Знайти
                        </x-button>
                    </form>
                </x-card>

                <x-card>
                    <div class="d-flex flex-nowrap flex-md-wrap overflow-auto overflow-md-visible text-center w-100">
                        @foreach ($categories as $category)
                            @if ($category->products()->where('menu_id', 2)->exists())
                                <div class="flex-shrink-0 flex-md-fill px-2">
                                    <x-link-button
                                        class="px-3 py-2 small fw-medium w-100 min-w-120"
                                        :href="route('menu.show', [$menu] + [...request()->query(), 'categories' => $category->id])"
                                        :active="request('categories') == $category->id">
                                        {{ $category->name }}
                                    </x-link-button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </x-card>

                <x-card class="mt-3">
                    <div class="row g-2 justify-content-start">
                        <x-product.index :products="$products" :cart="$cart"/>
                    </div>
                </x-card>

            </div>
        </div>
    </div>
</x-layout>
