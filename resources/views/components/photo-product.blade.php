<div class="d-flex justify-content-center align-items-center" style="height: 200px; overflow: hidden;">
    <img
    src="{{ Storage::disk('public')->exists($product->image)
        ? asset('storage/' . $product->image)
        : (file_exists(public_path('images/menu/products/' . $product->image))
            ? asset('images/menu/products/' . $product->image)
            : asset('images/default.png')) }}"
    class="img-fluid rounded object-fit-cover w-100 h-100"
    alt="{{ $product->name }}">


</div>
