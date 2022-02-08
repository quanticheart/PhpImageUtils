FROM php:7.4-apache
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev  \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/  \
    && docker-php-ext-install gd \
    && pecl install imagick \
	&& docker-php-ext-enable imagick \

    #https://github.com/mlocati/docker-php-extension-installer
#    FROM php:7.4-apache
#    ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
#
#    RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
#        install-php-extensions gd xdebug imagick  \
#
#        FROM php:7.4-apache
#
#        RUN apt-get update && \
#        apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
#        docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
#        docker-php-ext-install gd

#FROM php:7.2-apache
#
#ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
#
#RUN chmod +x /usr/local/bin/install-php-extensions && \
#    install-php-extensions gd xdebug