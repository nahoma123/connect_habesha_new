# Use official PHP CLI image
FROM php:8.1-cli

# Set the working directory
WORKDIR /var/www/html

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    && docker-php-ext-enable mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Verify installed extensions (debugging step)
RUN php -m

# Optional: Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy your application files into the container (uncomment if needed)
# COPY . /var/www/html

# Expose a port for PHP's built-in server
EXPOSE 8080

# Command to run PHP's built-in server
CMD ["php", "-S", "0.0.0.0:8080"]
