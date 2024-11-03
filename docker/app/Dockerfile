FROM arm64v8/php:8.2-fpm

RUN useradd -m application

RUN rm -rf /etc/apt/keyrings
RUN mkdir /etc/apt/keyrings
RUN apt-get update
RUN apt-get install ca-certificates curl gnupg -y
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

RUN apt-get install -y \
		libfreetype6-dev nginx \
		libjpeg62-turbo-dev \
		libpng-dev libzip-dev procps libyaml-dev wget libpq-dev nodejs

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install zip
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install exif
RUN docker-php-ext-install bcmath
RUN apt-get install -y libicu-dev && docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-enable pdo_mysql zip pcntl exif pdo_pgsql pgsql bcmath intl
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug && docker-php-ext-enable xdebug


RUN apt-get install nodejs -y \
    npm \
    && npm install -g pnpm@latest

RUN wget -qO- https://get.pnpm.io/install.sh | ENV="$HOME/.bashrc" SHELL="$(which bash)" bash -

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir /home/application/.composer && \
    chown -R application: /home/application/.composer

RUN mkdir /app && mkdir /run/nginx
COPY copy/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
COPY copy/app.conf /etc/nginx/sites-enabled/default
COPY copy/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN chmod +x /usr/local/bin/docker-php-entrypoint
RUN sed 's@;date.timezone =@date.timezone = UTC@;s@max_execution_time = .*@max_execution_time = 72000@;s@memory_limit = .*@memory_limit = 512M@' /usr/local/etc/php/php.ini-production > /usr/local/etc/php/php.ini
RUN sed 's@user = .*@user = application@;s@group = .*@group = application@' /usr/local/etc/php-fpm.d/www.conf.default > /usr/local/etc/php-fpm.d/www.conf

RUN chown -R application: /app

WORKDIR /app
