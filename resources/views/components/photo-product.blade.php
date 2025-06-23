<div class="d-flex justify-content-start align-items-start">
    <img
        src="{{ file_exists(public_path('images/menu/products/' . $product->image))
            ? asset('images/menu/products/' . $product->image)
            : (Storage::disk('public')->exists($product->image)
                ? Storage::url($product->image)
                : asset('images/default.png')) }}"
        class="img-fluid rounded object-fit-cover w-100 w-sm-75 "
        alt="{{ $product->name }}">
</div>
