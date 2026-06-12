# ---------------------------------------------------------------------------
# Image PHP 8.3 FPM pour Symfony 7
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine

# Dépendances système + extensions PHP requises par Symfony/Doctrine
RUN apk add --no-cache \
        git \
        unzip \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        bash \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        opcache \
        zip

# Composer (copié depuis l'image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI (pratique pour les étudiants : serveur, vérifs, etc.)
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash \
    && apk add --no-cache symfony-cli || true

WORKDIR /var/www/html

# Au build, on ne copie rien : le code est monté en volume (cf docker-compose).
# On installe juste les dépendances au premier démarrage via le Makefile / entrypoint.

# OPcache : configuration raisonnable pour le développement
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Augmente les limites pour le dev
RUN { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
    } > /usr/local/etc/php/conf.d/symfony.ini

EXPOSE 9000
CMD ["php-fpm"]
