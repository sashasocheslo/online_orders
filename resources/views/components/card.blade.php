<article
    @if ($attributes->get('type') === 'product')
    {{ $attributes->merge([
        'class' => 'card shadow-sm p-3 rounded-3 border-0 d-flex flex-column text-center hover-shadow h-100',
        'style' => 'min-width: 300px;'
    ]) }}
    @elseif ($attributes->get('type') === 'cart')
        {{ $attributes->merge(['class' => 'card shadow-sm p-3 rounded-3 border-0 p-4 d-flex flex-column align-items-center', 'style' => 'width: 100%; max-width: 30rem;']) }}
    @elseif ($attributes->get('type') === 'modal')
        {{ $attributes->merge(['class' => 'card shadow-sm p-3 rounded-3 border-0 w-50 mx-auto p-4 bg-white shadow-lg rounded-4 d-flex flex-column align-items-center justify-content-center', 'style' => 'max-width: 300px;']) }}
    @else
        {{ $attributes->merge(['class' => 'card shadow-sm p-3 rounded-3 border-0 text-center d-flex flex-column align-items-center justify-content-center flex-fill', 'style' => 'margin-right: auto;']) }}
    @endif
>
    {{ $slot }}
</article>
