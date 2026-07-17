FROM php:8.4-fpm

WORKDIR /var/www

# Install dependencies + Node.js
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    libzip-dev \
    libonig-dev \
    curl \
    ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        pcntl \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# User non-root
RUN useradd -ms /bin/bash -u 1000 dev

USER dev

EXPOSE 9000

CMD ["php-fpm"]