@props([
    'categories',
    'product' => null,
])

<div class="mb-3">
    <label for="name" class="form-label">Назва <span class="text-danger">*</span></label>
    <input
        id="name"
        name="name"
        type="text"
        value="{{ old('name', $product?->name) }}"
        class="form-control @error('name') is-invalid @enderror"
        maxlength="255"
        required
    >
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="price" class="form-label">Ціна <span class="text-danger">*</span></label>
    <input
        id="price"
        name="price"
        type="number"
        value="{{ old('price', $product?->price) }}"
        class="form-control @error('price') is-invalid @enderror"
        min="0.01"
        max="99999999.99"
        step="0.01"
        required
    >
    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Опис</label>
    <textarea
        id="description"
        name="description"
        class="form-control @error('description') is-invalid @enderror"
        maxlength="1000"
        rows="4"
    >{{ old('description', $product?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="size" class="form-label">Розмір</label>
    <input
        id="size"
        name="size"
        type="text"
        value="{{ old('size', $product?->size) }}"
        class="form-control @error('size') is-invalid @enderror"
        maxlength="50"
    >
    @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="category_id" class="form-label">Категорія <span class="text-danger">*</span></label>
    <select
        id="category_id"
        name="category_id"
        class="form-select @error('category_id') is-invalid @enderror"
        required
    >
        <option value="">Оберіть категорію</option>
        @foreach ($categories as $category)
            <option
                value="{{ $category->id }}"
                @selected((string) old('category_id', $product?->category_id) === (string) $category->id)
            >
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="image" class="form-label">
        Зображення @if ($product === null)<span class="text-danger">*</span>@endif
    </label>
    <input
        id="image"
        name="image"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="form-control @error('image') is-invalid @enderror"
        @required($product === null)
    >
    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror

    @if ($product?->image)
        <img
            src="{{ asset('storage/'.$product->image) }}"
            alt="Поточне зображення {{ $product->name }}"
            width="150"
            class="mt-2 rounded shadow"
        >
    @endif
</div>
