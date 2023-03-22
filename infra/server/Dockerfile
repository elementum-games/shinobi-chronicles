FROM php:8.2.0-apache

COPY . /var/www/html

RUN apt-get update
RUN apt-get install -y default-mysql-client
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Install composer for the application dependencies
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /bin/composer

WORKDIR /var/www/html
RUN composer install -noa
