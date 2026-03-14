FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Install Apache, PHP, and MySQL client
RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysqli \
    php8.1-pdo \
    php8.1-mysql \
    libapache2-mod-php8.1 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers php8.1

# Set working directory
WORKDIR /var/www/html

# Remove default Apache page
RUN rm -f /var/www/html/index.html

# Copy all project files
COPY . /var/www/html/

# Configure Apache virtual host
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    DocumentRoot /var/www/html'; \
    echo '    <Directory /var/www/html>'; \
    echo '        Options Indexes FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/000-default.conf

# Make uploads folder writable
RUN mkdir -p /var/www/html/public/uploads/items \
    && chmod -R 775 /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html

# Startup script
RUN printf '#!/bin/bash\nphp /var/www/html/setup.php\napache2ctl -D FOREGROUND\n' > /start.sh \
    && chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
