
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "",         
    "database": "price_tracker",
    "charset": "utf8mb4",
    "autocommit": False
}


PARSER_CONFIG = {
    "timeout": 15,           
    "max_retries": 3,       
    "delay_between_requests": 1,  
    "user_agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    ),
}


LOG_CONFIG = {
    "log_file": "../logs/price_checker.log",
    "level": "INFO",         
    "max_size_mb": 5,        
}

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