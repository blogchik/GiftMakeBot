# PHP 8.1 FPM image
FROM php:8.1-fpm

# Set working directory
WORKDIR /app

# Copy API Gateway files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Expose FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]