FROM php

RUN apt-get update && apt-get install -qqy git && rm -r /var/lib/apt/lists/*
RUN git config --global user.email "gitelephant@cypresslab.net"
RUN git config --global user.name "gitelephant"

RUN docker-php-ext-install mbstring
RUN pecl install xdebug
RUN echo "zend_extension = `php-config --extension-dir`/xdebug.so" >> /usr/local/etc/php/php.ini

WORKDIR /app