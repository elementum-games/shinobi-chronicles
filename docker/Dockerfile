FROM php:8.2.0-apache
ARG ENV
ARG WEB_URL
ARG DB_HOST
ARG DB_NAME
ARG DB_USER
ARG DB_PASSWORD


RUN apt-get update
RUN apt-get install -y default-mysql-client git zip unzip
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install composer for the application dependencies
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /bin/composer

COPY /docker/sendmail /usr/sbin/sendmail
RUN chmod +x /usr/sbin/sendmail

COPY . /var/www/html
WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV DB_HOST $DB_HOST
ENV DB_USER $DB_USER
ENV DB_PASSWORD $DB_PASSWORD

RUN composer install -noa
COPY /docker/example.vars.php ./secure/vars.php
RUN sed -i "s/__ENV__/${ENV}/" ./secure/vars.php && \
    sed -i "s%__URL__%${WEB_URL}%" ./secure/vars.php && \
    sed -i "s%__DB_HOST__%${DB_HOST}%" ./secure/vars.php && \
    sed -i "s/__DB_USER__/${DB_USER}/" ./secure/vars.php && \
    sed -i "s/__DB_PASSWORD__/${DB_PASSWORD}/" ./secure/vars.php && \
    sed -i "s/__DB_NAME__/${DB_NAME}/" ./secure/vars.php

ENTRYPOINT ["./docker/seed_db.sh"]
CMD ["apache2-foreground"]