FROM php:7.4-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug-3.1.6 && docker-php-ext-enable xdebug
RUN apt update && apt install -y libldap2-dev && docker-php-ext-install ldap
RUN apt update && apt install zip unzip && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite && service apache2 restart