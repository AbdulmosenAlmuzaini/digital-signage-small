FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# تفعيل mod_rewrite (ليس ضرورياً جداً هنا لكن مفيد)
RUN a2enmod rewrite

WORKDIR /var/www/html

# Increase upload limits for video files
RUN echo "upload_max_filesize = 500M\npost_max_size = 500M\nmemory_limit = 512M" > /usr/local/etc/php/conf.d/uploads.ini
