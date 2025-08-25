# FastFood — Вебресурс для онлайн-замовлення їжі

## Опис
**FastFood** — це вебресурс для онлайн-замовлення швидкої їжі, розроблений на **Laravel** з підтримкою онлайн-оплат через **Stripe**.  
Проєкт використовує **Docker** для зручного розгортання та включає **RESTful API** для взаємодії з додатком.

---

## Основні можливості
- Реєстрація та авторизація користувачів  
- Перегляд меню ресторанів  
- Отримання списку продуктів у меню
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
- **Backend:** PHP 8.x, Laravel 11 
- **Frontend:** Blade, Bootstrap 5, HTML5, CSS3  
- **База даних:** MariaDB / MySQL  
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

- `GET /menus` — отримати список меню  
- `GET /menus/{id}` — переглянути конкретне меню  
- `GET /menus/{id}/products` — список продуктів у меню  
- `POST /menus/{id}/products` — додати продукт у меню  
- `PUT /menus/{id}/products/{id}` — оновити продукт  
- `DELETE /menus/{id}/products/{id}` — видалити продукт  
- `POST /products/{id}/comments` — додати коментар  
- `GET /cart-products` — перегляд кошика  
- `POST /payments` — оплата через Stripe  

---

## Docker — запуск проєкту

### Вимоги
- **Docker**  
- **Docker Compose**  
- **PHP >= 8.1**, **Composer** (для локальної роботи без контейнерів)  

### Інструкція з запуску
```bash
# 1. Клонувати репозиторій
git clone https://github.com/username/fastfood.git
cd fastfood

# 2. Запустити контейнери
docker-compose up -d

# 3. Виконати міграції та сідери
docker-compose exec app php artisan migrate --seed

# 4. Зупинити контейнери
docker-compose down

# 5. Додаткові команди
# Встановити залежності
docker-compose exec app composer install

# Кешувати конфігурації
docker-compose exec app php artisan config:cache

# Запустити Tinker для роботи з моделями
docker-compose exec app php artisan tinker
