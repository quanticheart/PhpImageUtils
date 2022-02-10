FROM php:7.4-apache
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev  \
    && docker-php-ext-configure gd --with-freetype --with-jpeg  \
    && docker-php-ext-install gd \
    && docker-php-ext-install exif  \
    && docker-php-ext-enable exif \
    && pecl install imagick \
	&& docker-php-ext-enable imagick \
