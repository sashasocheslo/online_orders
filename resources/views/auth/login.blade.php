<x-layout>
    <div class="container d-flex justify-content-center" style="min-height: 30vh; padding-top: 30px;">
        <div class="w-100" style="max-width: 600px;">
            <h1 class="text-center mb-4 text-dark fw-bold">Вхід</h1>

            <x-card class="p-5 border rounded-4 w-100">

                <form action="{{ route('login.store') }}" method="POST" class="w-100">
                    @csrf
                    <div class="mb-4 w-100">
                        <x-label for="email" :required="true">Електрона пошта</x-label>
                        <x-text-input id="email" name="email" />
                    </div>

                    <div class="mb-4 w-100">
                        <x-label for="password" :required="true">Пароль</x-label>
                        <x-text-input id="password" name="password" type="password"/>
                    </div>

                    <div class="mb-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center">
                            <input type="checkbox" id="remember" name="remember"
                                class="form-check-input me-2 align-self-center" />
                            <label for="remember" class="m-0 small">Запам’ятати мене</label>
                        </div>
                    </div>

                    <div class="d-grid w-100">
                        <x-button>Увійти</x-button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="{{ route('auth.google') }}" class="btn btn-danger shadow rounded px-3 py-2">
                        Увійти за допомогою Google
                    </a>
                </div>

                <p class="mt-3 text-center">
                    Немає акаунту? <a href="{{ route('register') }}">Зареєструватися</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layout>
