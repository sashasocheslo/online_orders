<x-card class="shadow-lg rounded-3 p-3 text-center">
    <div class="card-body">
        <div>
            <x-photo-menu :$menu class="mb-3"/>
            <h3 class="card-title text-danger fw-bold">
                {{ $menu->name }}
            </h3>
        </div>
    </div>
</x-card>
