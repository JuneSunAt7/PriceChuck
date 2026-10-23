import mysql.connector
import requests
from bs4 import BeautifulSoup
import re
import logging
import sys
from datetime import datetime
from config import DB_CONFIG, PARSER_CONFIG

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(PARSER_CONFIG["log_file"], encoding='utf-8'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)


def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)


def extract_price_from_text(text: str):
    patterns = [
        r'(\d{1,3}(?:\s\d{3})+)\s*(?:₽|руб|р\.|RUB)?',
        r'(\d{3,})\s*(?:₽|руб|р\.|RUB)?',
    ]

    prices = []
    for pattern in patterns:
        matches = re.findall(pattern, text)
        for m in matches:
            try:
                price = float(m.replace(' ', ''))
                if 10 < price < 10_000_000:
                    prices.append(price)
            except ValueError:
                continue

    return max(prices) if prices else None


def parse_ozon(soup: BeautifulSoup):
    price_elem = soup.find('span', attrs={'data-widget': 'webPrice'})
    if price_elem:
        text = price_elem.get_text()
        return extract_price_from_text(text)

    price_elem = soup.find(string=re.compile(r'\d+\s*₽'))
    if price_elem:
        return extract_price_from_text(price_elem)

    return None


def parse_wildberries(soup: BeautifulSoup):
    price_elem = soup.find('span', class_='price-block__final-price')
    if price_elem:
        return extract_price_from_text(price_elem.get_text())
    return None


def parse_generic(soup: BeautifulSoup):
    meta_price = soup.find('meta', property='product:price:amount')
    if meta_price and meta_price.get('content'):
        try:
            return float(meta_price['content'])
        except ValueError:
            pass

    common_classes = ['price', 'product-price', 'current-price', 'sale-price']
    for cls in common_classes:
        elem = soup.find(class_=re.compile(cls, re.I))
        if elem:
            price = extract_price_from_text(elem.get_text())
            if price:
                return price

    return extract_price_from_text(soup.get_text())


def parse_title(soup: BeautifulSoup, url: str) -> str:
    meta_title = soup.find('meta', property='og:title')
    if meta_title and meta_title.get('content'):
        return meta_title['content'][:255]

    if soup.title and soup.title.string:
        return soup.title.string.strip()[:255]

    return url[:100]


def get_price_and_title(url: str):
    headers = {'User-Agent': PARSER_CONFIG["user_agent"]}

    try:
        response = requests.get(url, headers=headers, timeout=PARSER_CONFIG["timeout"])
        response.raise_for_status()
        soup = BeautifulSoup(response.text, 'html.parser')

        if 'ozon.ru' in url:
            price = parse_ozon(soup)
        elif 'wildberries.ru' in url:
            price = parse_wildberries(soup)
        else:
            price = parse_generic(soup)

        title = parse_title(soup, url)

        return price, title

    except requests.RequestException as e:
        logger.error(f"Ошибка запроса {url}: {e}")
        return None, None
    except Exception as e:
        logger.error(f"Неожиданная ошибка {url}: {e}")
        return None, None


def check_all_prices():
    logger.info("=" * 50)
    logger.info(f"Запуск проверки цен: {datetime.now()}")

    conn = get_db_connection()
    cursor = conn.cursor()

    try:
        cursor.execute("SELECT id, url, title FROM products")
        products = cursor.fetchall()

        logger.info(f"Найдено товаров для проверки: {len(products)}")

        updated = 0
        for product_id, url, title in products:
            logger.info(f"Проверяю: {url}")

            price, new_title = get_price_and_title(url)

            if price is None:
                logger.warning(f"   Не удалось получить цену")
                continue

            cursor.execute(
                "SELECT price FROM price_history WHERE product_id = %s ORDER BY checked_at DESC LIMIT 1",
                (product_id,)
            )
            last_row = cursor.fetchone()
            last_price = float(last_row[0]) if last_row else None

            cursor.execute(
                "INSERT INTO price_history (product_id, price) VALUES (%s, %s)",
                (product_id, price)
            )

            if not title and new_title:
                cursor.execute(
                    "UPDATE products SET title = %s WHERE id = %s",
                    (new_title, product_id)
                )

            if last_price is not None and price < last_price:
                cursor.execute(
                    "INSERT INTO notifications (product_id, old_price, new_price) VALUES (%s, %s, %s)",
                    (product_id, last_price, price)
                )
                logger.info(f"  📉 Цена упала: {last_price} → {price}")
            elif last_price is None:
                logger.info(f"  ✓ Первая проверка: {price}")
            else:
                logger.info(f"  ✓ Цена: {price} (было: {last_price})")

            updated += 1

        conn.commit()
        logger.info(f"Готово. Обновлено товаров: {updated}")

    except Exception as e:
        logger.error(f"Ошибка при работе с БД: {e}")
        conn.rollback()
    finally:
        cursor.close()
        conn.close()


if __name__ == "__main__":
    check_all_prices()