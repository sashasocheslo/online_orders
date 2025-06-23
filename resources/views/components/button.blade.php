<button {{
    $attributes->merge([
        'class' => collect([
            str($c = $attributes->get('class', ''))->contains('btn-fullwidth')
                ? 'btn fw-bold w-100'
                : 'btn fw-bold',

            str($c)->contains('btn-close') || str($c)->contains('btn-quantity-minus') || str($c)->contains('btn-quantity-plus') ? 'rounded-circle' :
            (str($c)->contains('btn-size') ? 'rounded-pill' :
            (str($c)->contains('btn-buy') ? 'rounded px-4 py-2 fs-6' : 'rounded')),

            // Цвет кнопки
            str($c)->contains('btn-danger-custom') ? 'btn-danger text-white' :
            (str($c)->contains('btn-close') ? 'btn-close bg-secondary-subtle text-secondary btn-outline-secondary' :
            (str($c)->contains('btn-quantity-minus') ? 'bg-light text-secondary border-0' :
            (str($c)->contains('btn-quantity-plus') ? 'bg-success-subtle text-success border-0' : 'btn-primary text-white border-0'))),
        ])->implode(' '),

        'data-bs-toggle' => str($c)->contains('btn-close') ? null : 'modal',
        'data-bs-target' => str($c)->contains('btn-close') || empty($name ?? '') ? null : '#modal-' . \Illuminate\Support\Str::slug($name),
        'data-bs-dismiss' => str($c)->contains('btn-close') ? 'modal' : null,
        'aria-label' => str($c)->contains('btn-close') ? 'Закрити' : null,
        'disabled' => $attributes->get('disabled'),
    ])
}}>
    {{ $slot }}
</button>
