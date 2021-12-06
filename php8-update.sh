#!/bin/sh

# go to "php-8": the working directory
cd ..
if [ ! -e "php-8" ]; then
    mkdir "php-8"
fi
cd php-8

### WORKFLOW: UPDATE DEPENDENCIES ###
read -p "Update build package dependencies? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

    apt-get update
    # Copied from PHP7 + PHP7.4
    apt-get install g++ libzip-dev autoconf libfcgi-dev libfcgi0ldbl libjpeg62-turbo-dev libmcrypt-dev libssl-dev libc-client2007e libc-client2007e-dev libxml2-dev libbz2-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libfreetype6-dev libkrb5-dev libpq-dev libxml2-dev libxslt1-dev libmagickwand-dev libsqlite3-dev libonig-dev libwebp-dev libxpm-dev
    apt-get install git
    ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a
fi

### WORKFLOW: ASK VERSION ###
#VERSION=
#while [ -z $VERSION ]; do
#    echo -n 'Version? f.e. 8.0.0 '
#    read VERSION
#done

DIRECTORY_TARGET=

### WORKFLOW: FETCH ###

if [ -e "download" ]; then
    rm download
fi

wget -O download https://config.bett-ingenieure.de/getLatestVersion?target=php-8.0.tar.gz

if [ $? -ne 0 ]; then
    echo "failed to fetch" >&2
    exit 1
fi

DIRECTORY_TARGET=$(tar -tzf download | head -1 | cut -f1 -d"/")

if [ -e "${DIRECTORY_TARGET}" ]; then
    rm -R ${DIRECTORY_TARGET}
fi

tar xfvz download

if [ $? -ne 0 ]; then
    echo "failed to untar" >&2
    exit 1
fi

chown -R root:root ${DIRECTORY_TARGET}

rm download

cd ./${DIRECTORY_TARGET}

### WORKFLOW: CONFIGURE ###

./configure \
    --prefix=/opt/php-8/${DIRECTORY_TARGET}/ \
    --enable-mbstring \
    --enable-soap \
    --enable-calendar \
    --with-curl \
    --enable-gd \
    --with-freetype \
    --disable-rpath \
    --with-bz2 \
    --with-zlib \
    --with-zip \
    --with-pear \
    --enable-sockets \
    --enable-sysvsem \
    --enable-sysvshm \
    --enable-pcntl \
    --enable-mbregex \
    --with-mhash \
    --with-pdo-mysql \
    --with-mysqli \
    --with-jpeg=/usr/include/ \
    --with-xpm=/usr/include/ \
    --with-webp=/usr/include/ \
    --with-openssl \
    --with-fpm-user=www-data \
    --with-fpm-group=www-data \
    --with-libdir=/lib/x86_64-linux-gnu \
    --enable-ftp \
    --with-imap \
    --with-imap-ssl \
    --with-kerberos \
    --with-gettext \
    --with-libxml \
    --enable-fpm \
    --enable-intl \
    --enable-bcmath \
    --enable-exif \
    --enable-shmop \
    --enable-sysvmsg

if [ $? -ne 0 ]; then
    echo "failed to configure" >&2
    exit 1
fi

### WORKFLOW: MAKE ###

make -j 4

if [ $? -ne 0 ]; then
    echo "failed to make" >&2
    exit 1
fi

### WORKFLOW: INSTALL ###

make install

if [ $? -ne 0 ]; then
    echo "failed to install" >&2
    exit 1
fi

### WORKFLOW: EXTENSIONS ###

cd bin

./pecl -C ./pear.conf update-channels

if [ $? -ne 0 ]; then
    echo "Failed to update pecl" >&2
    exit 1
fi

echo Will install imagick...
yes '' | ./pecl install imagick

if [ $? -ne 0 ]; then
    echo "Failed to install imagick via pecl" >&2
    exit 1
fi

###
### ./pecl install imagick
###
### PECL IMAGICK is not compatible (27.11.2020) while installing 3.4.4:
### /tmp/pear/temp/imagick/imagick_file.c:313:112: error: expected ‘;’, ‘,’ or ‘)’ before ‘TSRMLS_DC’
###  zend_bool php_imagick_stream_handler(php_imagick_object *intern, php_stream *stream, ImagickOperationType type TSRMLS_DC)
###                                                                                                                ^~~~~~~~~
### make: *** [Makefile:209: imagick_file.lo] Fehler 1
###
### Fetching master from github?

### Same for "apcu_bc-beta"
###

echo Will install apcu...
yes '' | ./pecl install apcu

if [ $? -ne 0 ]; then
    echo "Failed to install imagick apcu via pecl" >&2
    exit 1
fi

echo Will install redis...
yes '' | ./pecl install redis

if [ $? -ne 0 ]; then
    echo "Failed to install redis via pecl" >&2
    exit 1
fi

cd ..

### WORKFLOW: ACTIVATE ###

read -p "No errors? Should activate? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

    INIT_SCRIPT="/etc/init.d/php8-fpm"

    # update init script
    if [ -e "$INIT_SCRIPT" ]; then
        "$INIT_SCRIPT" stop
        rm "$INIT_SCRIPT"
    fi

    ln -s /opt/php-build-config/php8-fpm "$INIT_SCRIPT"
    chmod +x /opt/php-build-config/php8-fpm

    cd lib/
    ln -s ../../../php-build-config/php8.ini php.ini
    cd ..
    cd ..

    rm current-live
    ln -s ${DIRECTORY_TARGET} current-live

    "$INIT_SCRIPT" start

fi
