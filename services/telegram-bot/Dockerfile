# PHP 8.1 FPM image
FROM php:8.1-fpm

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Expose FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"] 
