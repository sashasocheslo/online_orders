# FastFood — Вебресурс для онлайн-замовлення їжі

## Опис
**FastFood** — це вебресурс для онлайн-замовлення швидкої їжі, розроблений на **Laravel** з підтримкою онлайн-оплат через **Stripe**.  
Проєкт використовує **Docker** для зручного розгортання та включає **RESTful API** для взаємодії з додатком.

---

## Основні можливості
- Реєстрація та авторизація користувачів  
- Перегляд меню ресторанів  
- Отримання списку товарів у меню
- Оновлення інформації про товар
- Видалення товару
- Додавання товарів у кошик  
- Оформлення та оплата замовлень через **Stripe**  
- Перегляд історії замовлень (реалізовано через кошик)  
- Додавання та видалення коментарів до товарів (для авторизованих користувачів)  
- Реалізація пошуку товару за допомогою заповнення форми
- Фільтрація за категорією
- Фільтрація за ціновим діапазоном
- RESTful API для взаємодії з меню, товарами, коментарями та кошиком  

---

## Технології
- **Backend:** PHP 8.5, Laravel 13
- **Frontend:** Blade, Bootstrap 5, HTML5, CSS3  
- **База даних:** MariaDB / MySQL  
- **API authentication:** Laravel Sanctum
- **API платежів:** Stripe  
- **Інфраструктура:** Docker, Docker Compose  
- **IDE:** Visual Studio Code  

---

## Архітектура
- Клієнт-серверна архітектура  
- Використано **MVC-патерн** (Model-View-Controller)  
- **RESTful API** для роботи з даними  

---

## RESTful API — основні маршрути

Публічні маршрути:

- `POST /api/auth/register` — зареєструвати користувача та отримати Bearer token;
- `POST /api/auth/login` — увійти та отримати Bearer token;
- `GET /api/menus` — отримати список меню;
- `GET /api/menus/{menu}` — переглянути конкретне меню;
- `GET /api/menus/{menu}/products` — отримати товари меню.

Захищені маршрути вимагають заголовок `Authorization: Bearer <token>`:

- `GET /api/auth/me` — отримати поточного користувача;
- `DELETE /api/auth/logout` — відкликати поточний token;
- `GET|POST /api/cart-products` — переглядати кошики та додавати товар;
- `PATCH|DELETE /api/cart-products/{cart_product}` — змінювати або видаляти власну позицію;
- `POST /api/menus/{menu}/products` — створити товар відповідно до policy;
- `PATCH|DELETE /api/menus/{menu}/products/{product}` — оновити або видалити товар відповідно до policy;
- `POST /api/menus/{menu}/products/{product}/comments` — додати коментар;
- `DELETE /api/comments/{comment}` — видалити дозволений policy коментар.

Plain-text token показується лише у відповіді register/login. У базі даних Sanctum зберігає його hash.

---

## Docker — запуск проєкту

### Вимоги
- **Docker**  
- **Docker Compose**  
- **PHP >= 8.5**, **Composer** (для локальної роботи без контейнерів)

### Інструкція з запуску
```bash
# 1. Клонувати репозиторій
git clone https://github.com/sashasocheslo/online_orders.git
cd online_orders

# 2. Створити файл .env на основі .env.example
cp .env.example .env

У файлі .env потрібно вказати параметри підключення до бази даних:

DB_CONNECTION=mysql
DB_HOST=mariadb_db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=root

# 3. Встановити залежності
docker-compose run --rm app composer install

# 4. Згенерувати ключ додатку
docker-compose run --rm app php artisan key:generate

# 5. Запустити контейнери
docker-compose up -d

# 6. Виконати міграції та сідери
docker-compose exec app php artisan migrate --seed

# 7. Зупинити контейнери
docker-compose down
