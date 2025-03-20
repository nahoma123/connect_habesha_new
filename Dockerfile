FROM php:7.4-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli

# Install Xdebug
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug

# Configure Xdebug for remote debugging
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
