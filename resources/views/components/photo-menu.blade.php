<div class="d-flex justify-content-center align-items-center" style="height: 150px;">
    <img
        src="{{ file_exists(public_path('images/menu/' . $menu->image))
            ? asset('images/menu/' . $menu->image)
            : (Storage::disk('public')->exists($menu->image)
                ? Storage::url($menu->image)
                : asset('images/default.png')) }}"
        class="img-fluid h-100 object-fit-contain"
        alt="{{ $menu->name }}">
</div>
