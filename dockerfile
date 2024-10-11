FROM php:8.2-apache-buster
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    pkg-config \
    unzip \
    vim \
    libfreetype6-dev \
    libjpeg-dev \
    libwebp-dev \
    libpng-dev \
    libxml2-dev \
    python3-pip \
    && pip3 install s3cmd \
    && docker-php-ext-install pdo pdo_mysql zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl mbstring xml \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/html
COPY . .
RUN composer install --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data var/log
RUN chown -R www-data:www-data var/cache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
EXPOSE 80
CMD ["apache2-foreground"]