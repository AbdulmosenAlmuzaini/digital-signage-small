FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Fix for Railway MPM error
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

RUN a2enmod rewrite

# Increase upload limits for video files
RUN echo "upload_max_filesize = 500M\npost_max_size = 500M\nmemory_limit = 512M" > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Copy the application files into the container for production
COPY ./signage /var/www/html/

# Ensure proper permissions for uploads
RUN chown -R www-data:www-data /var/www/html

# Set default PORT for local development, Railway will override this
ENV PORT=80

# Create an entrypoint script to dynamically replace the port at runtime
RUN echo '#!/bin/bash\n\
sed -i "s/Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf\n\
sed -i "s/<VirtualHost \*:.*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf\n\
exec apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
