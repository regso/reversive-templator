FROM php:7.4-fpm

# upgrade
RUN apt-get update && apt-get upgrade -y

# locale
RUN apt-get install -y locales
RUN sed -i -e  's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen \
   && locale-gen \
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:ru
ENV LC_LANG ru_RU.UTF-8
ENV LC_ALL ru_RU.UTF-8

# timezone
RUN echo "Etc/UTC" > /etc/timezone
RUN dpkg-reconfigure -f noninteractive tzdata

RUN pecl install xdebug

RUN docker-php-ext-enable xdebug

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# clear
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# conf
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY additional.ini /usr/local/etc/php/conf.d/

# user
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
USER www

WORKDIR /var/www
CMD ["php-fpm"]