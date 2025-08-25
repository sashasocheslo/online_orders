<x-card :type="$attributes->get('type') ?? 'product'">
    <div class="d-flex flex-column {{ $attributes->get('type') === 'cart' ? 'align-items-center' : 'text-center' }} w-100">
        <x-photo-product :$product />
        <h5 class="fw-bold text-dark mt-2 mb-1">{{ $product->name }}</h5>
        <p class="text-secondary mb-1">{{ $product->description }}</p>
        <div class="fw-normal text-dark fs-5 text-nowrap">
            {{ number_format($product->price, 2, ',', ' ') }} ₴
        </div>
        <div class="text-muted small mb-2">
            Категорія: {{ $product->category->name }}
        </div>

        <div class="mt-2">
            {{ $slot }}

        <a href="{{ route('menu.products.edit', [$product->menu_id, $product->id]) }}"
        class="btn btn-warning btn-sm mt-2">
            Редагувати
        </a>

          <form action="{{ route('menu.products.destroy', [$product->menu_id, $product->id]) }}"
                  method="POST" class="d-inline"
                  onsubmit="return confirm('Ви впевнені, що хочете видалити цей товар?')">
                @csrf
                @method('DELETE')
                <x-button>
                    Видалити
                </x-button>
            </form>
        </div>

        <div class="comments mt-3 text-start">
            <h6>Коментарі:</h6>

            @forelse($product->comments as $comment)
                <div class="border rounded p-2 mb-2">
                    <strong>{{ $comment->user->name ?? 'Анонім' }}:</strong>
                    <p class="mb-0">{{ $comment->content }}</p>
                </div>
            @empty
                <p class="text-muted">Ще немає коментарів.</p>
            @endforelse

            <h6>Коментарі:</h6>
            @foreach($product->comments as $comment)
                <div class="border p-2 mb-2 rounded">
                    <strong>{{ $comment->user->name ?? 'Анонім' }}:</strong>
                    <p>{{ $comment->content }}</p>

                    @if(auth()->id() == $comment->user_id)
                        <form action="{{ route('comments.destroy', ['comment' => $comment->id]) }}" method="POST" class="d-inline mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Видалити</button>
                        </form>

                    @endif
                </div>
            @endforeach

            @auth
                <form action="{{ route('products.comments.store', [$product->menu_id, $product->id]) }}" method="POST" class="mt-2">
                    @csrf
                    <textarea name="content" class="form-control mb-2" placeholder="Ваш коментар..." rows="2"></textarea>
                    <x-button>Додати коментар</x-button>
                </form>
            @else
                <p class="text-muted">Увійдіть, щоб додати коментар.</p>
            @endauth
        </div>

    </div>
</x-card>
