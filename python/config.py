"""
Конфигурация парсера цен.
Простой вариант — всё в одном файле.
"""

# Настройки подключения к MySQL
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "",          # ← Укажи свой пароль
    "database": "price_tracker",
    "charset": "utf8mb4",
    "autocommit": False
}

# Настройки парсера
PARSER_CONFIG = {
    "timeout": 15,           # Таймаут HTTP-запроса (сек)
    "max_retries": 3,        # Количество попыток при ошибке
    "delay_between_requests": 1,  # Задержка между запросами (сек)
    "user_agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    ),
}

# Настройки логирования
LOG_CONFIG = {
    "log_file": "../logs/price_checker.log",
    "level": "INFO",         # DEBUG, INFO, WARNING, ERROR
    "max_size_mb": 5,        # Макс. размер лог-файла
}

# Настройки уведомлений (можно добавить email/telegram)
NOTIFICATION_CONFIG = {
    "enabled": True,
    "email": {
        "smtp_server": "smtp.gmail.com",
        "smtp_port": 587,
        "username": "your_email@gmail.com",
        "password": "your_app_password",
        "to": "your_email@gmail.com"
    }
}