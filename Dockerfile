FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . /var/www/html/

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Make uploads folder writable
RUN mkdir -p /var/www/html/public/uploads/items \
    && chmod -R 775 /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads

# Startup script: run DB setup then start Apache
RUN echo '#!/bin/bash\nphp /var/www/html/setup.php\napache2-foreground' > /start.sh \
    && chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
