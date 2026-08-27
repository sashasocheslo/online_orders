# FastFood — онлайн-замовлення їжі з AI-помічником

## Про проєкт

**FastFood** — дипломний вебзастосунок на Laravel для перегляду меню ресторанів, формування окремих кошиків, створення замовлень і безпечної тестової оплати через Stripe. Авторизований користувач також може отримувати контекстні рекомендації від Gemini, OpenAI або Claude.

Застосунок запускається через Docker Compose. Автоматичні тести використовують ізольовану SQLite-базу в пам’яті та не виконують реальних платежів або платних AI-викликів.

## Основні можливості

- локальна реєстрація, вхід і Google OAuth;
- ролі користувача й адміністратора, policies та захищені маршрути;
- меню McDonald’s, KFC і Domino’s Pizza;
- пошук товарів, категорії та фільтрація за ціною;
- безпечний CRUD товарів із валідацією й керуванням зображеннями;
- окремий кошик для кожного ресторану;
- транзакційне створення замовлення зі snapshot-позиціями та історією статусів;
- Stripe Checkout у test mode, idempotency та перевірений webhook;
- Bearer-token API authentication через Laravel Sanctum;
- AI-рекомендації з контекстом діалогу, перевіркою structured output, retries і локальним fallback;
- offline AI evaluation на фіксованому dataset без доступу до мережі;
- request ID, безпечне логування повільних запитів, rate limits і базові security headers.

## Технології

- **Backend:** PHP 8.5, Laravel 13;
- **Frontend:** Blade, Bootstrap 5, JavaScript;
- **База даних:** MariaDB 11.3;
- **Тестування:** Pest 4, SQLite `:memory:`;
- **API authentication:** Laravel Sanctum;
- **Інтеграції:** Stripe, Google OAuth, Gemini, OpenAI, Claude;
- **Інфраструктура:** Docker, Docker Compose, Nginx, PHP-FPM.

## Архітектура

Контролери приймають HTTP-запити й передають основну роботу сервісам. Form Requests відповідають за валідацію, policies — за права доступу, Eloquent-моделі — за дані та зв’язки. Зовнішні Stripe й AI-провайдери приховані за інтерфейсами, тому в тестах їх можна замінити безпечними fake-реалізаціями.

```text
Browser / API
      ↓
Routes → Middleware → Controllers → Services → Eloquent → MariaDB
                                  ↘ AI providers / Stripe
```

## Запуск через Docker

### Вимоги

- Docker Desktop із Docker Compose;
- Git.

### Встановлення

```powershell
git clone https://github.com/sashasocheslo/online_orders.git
cd online_orders

Copy-Item .env.example .env

docker compose build
docker compose run --rm app composer install
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Після запуску сайт доступний за адресою `http://127.0.0.1:8000`, а health endpoint — `http://127.0.0.1:8000/up`.

Стандартні параметри MariaDB у `.env.example` уже відповідають `docker-compose.yml`. Ключі Stripe, Google OAuth та AI-провайдерів необов’язкові для локального перегляду й автоматичних тестів. Реальні секрети потрібно зберігати лише в `.env` і ніколи не додавати до Git.

Зупинка контейнерів:

```powershell
docker compose down
```

## Автоматичні перевірки

```powershell
docker compose exec app ./vendor/bin/pint --dirty
docker compose exec app php artisan test
docker compose exec app php artisan ai:evaluate
```

`ai:evaluate` за замовчуванням запускає лише локальний baseline. Live evaluation зовнішнього AI потребує явного параметра `--allow-network` і може створювати витрати.

## REST API

Публічні маршрути:

- `POST /api/auth/register` — реєстрація й отримання Bearer token;
- `POST /api/auth/login` — вхід й отримання Bearer token;
- `GET /api/menus` — список ресторанів;
- `GET /api/menus/{menu}` — конкретне меню;
- `GET /api/menus/{menu}/products` — товари меню.

Захищені маршрути вимагають `Authorization: Bearer <token>` з ability `api:access`:

- `GET /api/auth/me` — поточний користувач;
- `DELETE /api/auth/logout` — відкликання поточного token;
- `GET|POST /api/cart-products` — робота з власними кошиками;
- `PATCH|DELETE /api/cart-products/{cart_product}` — зміна власної позиції;
- `POST|PATCH|DELETE /api/menus/{menu}/products/...` — керування товарами відповідно до policy;
- `POST /api/menus/{menu}/products/{product}/comments` — додавання коментаря;
- `DELETE /api/comments/{comment}` — дозволене policy видалення коментаря.

Plain-text token повертається лише під час register/login. У базі Sanctum зберігає його hash.

## Безпечна демонстрація

- Stripe використовувати лише в test mode;
- не показувати `.env`, API-ключі, webhook secret або Bearer tokens;
- для демонстрації без зовнішніх сервісів використати локальний вхід, fake-provider тести й offline AI evaluation;
- redirect зі Stripe не підтверджує оплату: статус `paid` встановлює лише webhook із валідним підписом і сумою.
