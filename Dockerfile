FROM php:7.2-alpine

RUN apk update \
    && apk add git zlib-dev \
	&& git config --global user.email "test@gitelephant.org" \
    && git config --global user.name "GitElephant tests"

RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
	&& docker-php-ext-install zip
