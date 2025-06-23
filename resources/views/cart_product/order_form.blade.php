<x-layout>
    <div class="d-flex justify-content-center align-items-center">
        <form action="{{ route('stripe.payment') }}" method="POST" class="w-100" style="max-width: 600px;">
            <h1 class="text-center mb-4 text-dark fw-bold">Оформлення замовлення</h1>
            @csrf

            <div class="mb-3">
                <label for="phone_number" class="form-label text-dark fw-semibold">Номер телефону <span class="text-danger">*</span></label>
                <x-text-input name="phone_number" />
            </div>

            <div class="mb-3">
                <label for="delivery_address" class="form-label text-dark fw-semibold">Адреса доставки <span class="text-danger">*</span></label>
                <x-text-input name="delivery_address" />
            </div>

            <div class="mb-3">
                <label for="country" class="form-label text-dark fw-semibold">Країна <span class="text-danger">*</span></label>
                <x-text-input name="country" />
            </div>

            <input type="hidden" name="amount" value="{{ request('amount', 0) }}">

            <div class="mt-4">
                <x-button class="btn-danger-custom w-100" type="submit">
                    Оформити замовлення {{ number_format(request('amount', 0), 2, ',', ' ') }} ₴
                </x-button>
            </div>
        </form>
    </div>
</x-layout>
