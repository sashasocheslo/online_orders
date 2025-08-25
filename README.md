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
