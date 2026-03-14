FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Disable ALL mpm modules and enable only prefork (fixes "More than one MPM" error)
RUN apt-get update && apt-get install -y apache2 \
    && a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite headers \
    && apt-get clean

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . /var/www/html/

# Allow .htaccess overrides
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    Options Indexes FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
} > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Make uploads folder writable
RUN mkdir -p /var/www/html/public/uploads/items \
    && chmod -R 775 /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads

# Startup script
RUN printf '#!/bin/bash\nphp /var/www/html/setup.php\napache2-foreground\n' > /start.sh \
    && chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
