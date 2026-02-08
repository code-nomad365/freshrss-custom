FROM freshrss/freshrss:latest

COPY extensions/ /var/www/FreshRSS/extensions/
RUN chown -R www-data:www-data /var/www/FreshRSS/extensions

# 備份預設 data 目錄，Volume 掛載會清空 data，啟動時需要還原
RUN cp -a /var/www/FreshRSS/data /var/www/FreshRSS/data-default

# 將資料還原邏輯注入原始 entrypoint 最前面
RUN sed -i '2i\
if [ ! -f /var/www/FreshRSS/data/config.php ]; then\
    echo "Volume 是空的，從預設資料還原...";\
    cp -a /var/www/FreshRSS/data-default/. /var/www/FreshRSS/data/;\
    chown -R www-data:www-data /var/www/FreshRSS/data;\
fi' /var/www/FreshRSS/Docker/entrypoint.sh
