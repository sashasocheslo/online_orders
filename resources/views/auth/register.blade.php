<x-layout>
    <div class="container d-flex justify-content-center" style="min-height: 30vh; padding-top: 30px;">
        <div class="w-100" style="max-width: 600px;">
            <h1 class="text-center mb-4 text-dark fw-bold">Реєстрація</h1>

            <x-card class="p-5 border rounded-4 w-100">

                <form action="{{ route('register.store') }}" method="POST" class="w-100">
                    @csrf

                    <div class="mb-4 w-100">
                        <x-label for="email" :required="true">Електрона пошта</x-label>
                        <x-text-input id="email" name="email" />
                    </div>

                    <div class="mb-4 w-100">
                        <x-label for="name" :required="true">Ім'я користувача</x-label>
                        <x-text-input id="name" name="name" />
                    </div>

                    <div class="mb-4 w-100">
                        <x-label for="password" :required="true">Пароль</x-label>
                        <x-text-input id="password" name="password" type="password"/>
                    </div>

                    <div class="mb-4 w-100">
                        <x-label for="password_confirmation" :required="true">Підтвердити пароль</x-label>
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"/>
                    </div>

                    <div class="mb-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center">
                            <input type="checkbox" id="remember" name="remember"
                            class="form-check-input me-2" style="transform: translateY(1px);" />
                            <label for="remember" class="form-check-label small">Запам’ятати мене</label>
                        </div>
                    </div>


                    <div class="d-grid w-100">
                        <x-button>Зареєструватися</x-button>
                    </div>
                </form>
                <p class="mt-3 text-center">
                   Є акаунт? <a href="{{ route('login') }}">Виконати вхід</a>
                </p>

            </x-card>
        </div>
    </div>
</x-layout>
