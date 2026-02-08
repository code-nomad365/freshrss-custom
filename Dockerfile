FROM freshrss/freshrss:latest

COPY extensions/ /var/www/FreshRSS/extensions/
RUN chown -R www-data:www-data /var/www/FreshRSS/extensions

# 備份預設 data 目錄，Volume 掛載會清空 data，啟動時需要還原
RUN cp -a /var/www/FreshRSS/data /var/www/FreshRSS/data-default

COPY entrypoint.sh /custom-entrypoint.sh
RUN chmod +x /custom-entrypoint.sh

ENTRYPOINT ["/custom-entrypoint.sh"]
CMD ["apache2-foreground"]
