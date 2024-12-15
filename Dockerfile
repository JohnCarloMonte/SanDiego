# Use the official PHP 8.1 image with Apache
FROM php:8.1-apache

# Set the working directory
WORKDIR /var/www/html

# Install additional PHP extensions (if needed)
# Example: Install mysqli and pdo_mysql for database support
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files to the container
COPY . /var/www/html

# Set permissions for the web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 (default for Apache)
EXPOSE 80

# Enable Apache mod_rewrite (if needed for frameworks like Laravel or CodeIgniter)
RUN a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]
