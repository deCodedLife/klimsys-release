FROM php:8.3-fpm

RUN apt-get update \
 && apt-get install -y \
      libzip-dev \
      libonig5 \
      libpng-dev


RUN docker-php-ext-install zip && docker-php-ext-enable zip
RUN docker-php-ext-install gd && docker-php-ext-enable gd
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql
RUN docker-php-ext-install mysqli
ADD ./docker/php/api /var/www/klimsystems
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a new user
RUN adduser --disabled-password --gecos '' developer

# Add user to the group
RUN chown -R developer:www-data /var/www
RUN chown developer /var/www
RUN chmod 755 /var/www

# Switch to this user
USER developer

WORKDIR /var/www/klimsystems
#CMD [ "composer", "install" ]

#RUN composer install
#RUN [ "php-fpm" ]