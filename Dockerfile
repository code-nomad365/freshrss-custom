FROM freshrss/freshrss:latest
COPY extensions/ /var/www/FreshRSS/extensions/
RUN chown -R www-data:www-data /var/www/FreshRSS/extensions
