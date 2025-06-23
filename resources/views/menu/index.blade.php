<x-layout>
    <div class="text-center my-4">
        <h2 class="fw-bold text-white">Оберіть відповідне меню закладу для замовлення</h2>
    </div>

        <div class="container-fluid mt-4 px-0 d-flex justify-content-center">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                @foreach ($menus as $menu)
                    <div class="col" onclick="window.location='{{ route('menu.show', $menu) }}'" role="button">
                        <div
                            class="h-100 text-center d-flex flex-column justify-content-between transition position-relative"
                            onmouseover="this.classList.add('shadow-lg')"
                            onmouseout="this.classList.remove('shadow-lg')">
                            <x-menu-card :$menu class="mb-3" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
