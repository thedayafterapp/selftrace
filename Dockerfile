FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    nodejs \
    npm \
    bash \
    su-exec \
    gcompat

# Install PHP extensions that aren't bundled in the base image
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    opcache

# Enable opcache (bundled but disabled by default)
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# iconv, ctype, mbstring, xml are all bundled in php:8.3-fpm-alpine — no install needed

# Install Tailwind CSS via npm — pure Node.js, no glibc issues on Alpine
RUN npm install -g tailwindcss@3

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json ./

# Install PHP dependencies
RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Copy application files
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Create var directory with correct permissions
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/var

# Copy and set up entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
