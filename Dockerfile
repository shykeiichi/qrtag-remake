FROM php:8.2-apache-bullseye
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && apt-get install -y libc-client-dev libkrb5-dev nmap inetutils-ping net-tools libpng-dev libxml2-dev libxslt1-dev libcurl4-openssl-dev zip unzip git libfreetype6-dev libjpeg62-turbo-dev libpng-dev && rm -r /var/lib/apt/lists/*
RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql gettext xsl pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN sed -i /etc/apache2/sites-enabled/000-default.conf -e 's,DocumentRoot /var/www/html, DocumentRoot /var/www/html/public,g' -e 's,:80,:8080,g'
RUN sed -i /etc/apache2/ports.conf -e 's,Listen 80,Listen 8080,g'
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs && apt-get clean
ENV APACHE_HTTP_PORT=8080
EXPOSE 8080
WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir /.config && chmod 777 /.config && chmod 777 /var/www/html/storage && chmod 777 /var/www/html/public/ && ls -la public && composer install --no-dev && npm install && npm run build && chmod 755 /var/www/html/public/