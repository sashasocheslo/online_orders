<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastFood</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .bg-fastfood {
            background: linear-gradient(45deg, var(--bs-danger) 0%, var(--bs-warning) 85%);
        }
    </style>

</head>
<body class="bg-fastfood text-white">
    <div class="container-fluid py-3 position-relative">
        <h1 class="d-block d-lg-none text-center fw-bold mb-0">FastFood</h1>

        <h1 class="d-none d-lg-block position-absolute start-50 translate-middle text-center fw-bold m-3">
            FastFood
        </h1>

        <nav class="d-none d-lg-flex position-absolute top-0 end-0 p-3 align-items-center gap-3">
            @auth
                <span class="text-white fs-5">{{ auth()->user()->name }}</span>
                <form action="{{ route('auth.destroy') }}" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-light btn-sm">Вийти</button>
                </form>
                <a href="{{ route('cart_product.index') }}" class="text-white fs-4">
                    <i class="bi bi-cart-fill"></i>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-white fs-5 d-flex align-items-center gap-2">
                    Увійти
                    <i class="bi bi-person-circle fs-4"></i>
                </a>
            @endauth
        </nav>

        <div class="d-block d-lg-none mt-3 text-center">
            @auth
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <span class="text-white fs-5">{{ auth()->user()->name }}</span>
                    <form action="{{ route('auth.destroy') }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-light btn-sm">Вийти</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}"
                   class="fs-5 d-flex justify-content-center align-items-center gap-2 text-dark bg-white px-3 py-1 rounded mt-2">
                    Увійти
                    <i class="bi bi-person-circle fs-4"></i>
                </a>
            @endauth
        </div>
    </div>

    <div
      class="container-fluid mt-5 p-4 rounded shadow bg-primary bg-gradient" style="min-height: 80vh;">
        @if (session('error'))
            <div role="alert" class="border-start border-4 border-danger bg-danger bg-opacity-10 p-4 mb-4 rounded">
                <p class="fw-bold text-danger mb-1">Error!</p>
                <p class="text-danger m-0">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div role="alert" class="border-start border-4 border-success bg-success bg-opacity-10 p-4 mb-4 rounded">
                <p class="fw-bold text-success mb-1">Success!</p>
                <p class="text-success m-0">{{ session('success') }}</p>
            </div>
        @endif
        {{ $slot }}
    </div>

    @include('components.product.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
