FROM php:8.2.0-apache

RUN apt-get update
RUN apt-get install -y default-mysql-client git
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Install composer for the application dependencies
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /bin/composer

COPY infra/server/sendmail /usr/sbin/sendmail
RUN chmod +x /usr/sbin/sendmail

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY . /var/www/html
RUN composer install -noa
