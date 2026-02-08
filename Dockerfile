FROM freshrss/freshrss:latest

COPY extensions/ /var/www/FreshRSS/extensions/
RUN chown -R www-data:www-data /var/www/FreshRSS/extensions

# 持久化資料目錄，避免重新部署時遺失設定
ENV DATA_PATH=/data/freshrss
RUN mkdir -p /data/freshrss && chown -R www-data:www-data /data/freshrss
VOLUME /data
