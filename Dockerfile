# Use the official PHP 7.4 Apache image as a base
FROM php:7.4-apache

# Set working directory
WORKDIR /var/www/html

# Install required PHP extensions (mysqli for your app, pdo_mysql often useful)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install Xdebug version 2.9.8
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug

# Configure Xdebug - Copy the custom INI file
COPY xdebug_custom.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug_custom.ini

# Expose port 80 (standard HTTP port used by Apache)
EXPOSE 80