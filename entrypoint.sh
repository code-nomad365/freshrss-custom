#!/bin/sh

# 如果 Volume 是空的（沒有 config.php），從備份複製預設資料
if [ ! -f /var/www/FreshRSS/data/config.php ]; then
    echo "Volume 是空的，從預設資料還原..."
    cp -a /var/www/FreshRSS/data-default/. /var/www/FreshRSS/data/
    chown -R www-data:www-data /var/www/FreshRSS/data
fi

# 執行 FreshRSS 原始的 entrypoint
exec /var/www/FreshRSS/Docker/entrypoint.sh "$@"
