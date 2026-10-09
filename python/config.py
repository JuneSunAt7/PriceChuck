"""
Конфигурация через классы.
Удобно для автодополнения в IDE.
"""

class DatabaseConfig:
    HOST = "localhost"
    PORT = 3306
    USER = "root"
    PASSWORD = ""              # ← Укажи свой пароль
    DATABASE = "price_tracker"
    CHARSET = "utf8mb4"
    
    @classmethod
    def as_dict(cls):
        return {
            "host": cls.HOST,
            "port": cls.PORT,
            "user": cls.USER,
            "password": cls.PASSWORD,
            "database": cls.DATABASE,
            "charset": cls.CHARSET,
        }


class ParserConfig:
    TIMEOUT = 15
    MAX_RETRIES = 3
    DELAY = 1
    USER_AGENT = (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36"
    )


class LogConfig:
    LOG_FILE = "../logs/price_checker.log"
    LEVEL = "INFO"