FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# تفعيل mod_rewrite (ليس ضرورياً جداً هنا لكن مفيد)
RUN a2enmod rewrite

# Update Apache to use the PORT environment variable (Railway requirement)
ENV PORT=80
RUN sed -s -i -e "s/80/\${PORT}/" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

WORKDIR /var/www/html

# Increase upload limits for video files
RUN echo "upload_max_filesize = 500M\npost_max_size = 500M\nmemory_limit = 512M" > /usr/local/etc/php/conf.d/uploads.ini

# Copy the application files into the container for production
COPY ./signage /var/www/html/

# Ensure proper permissions for uploads
RUN chown -R www-data:www-data /var/www/html
