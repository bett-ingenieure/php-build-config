#!/bin/sh

# go to "php-7": the working directory
cd ..
if [ ! -e "php-7.4" ]; then
    mkdir "php-7.4"
fi
cd php-7.4

### WORKFLOW: UPDATE DEPENDENCIES ###
read -p "Update build package dependencies? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

    apt-get update
    # PHP7 needed, here are only the additional packages listed
    apt-get install libsqlite3-dev libonig-dev libwebp-dev libxpm-dev
fi

### WORKFLOW: ASK VERSION ###
#VERSION=
#while [ -z $VERSION ]; do
#    echo -n 'Version? f.e. 7.4.1 '
#    read VERSION
#done

DIRECTORY_TARGET=

### WORKFLOW: FETCH ###

if [ -e "download" ]; then
    rm download
fi

wget -O download https://config.bett-ingenieure.de/getLatestVersion?target=php-7.4.tar.gz

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
    --prefix=/opt/php-7.4/${DIRECTORY_TARGET}/ \
    --enable-mbstring \
    --enable-soap \
    --enable-calendar \
    --with-curl \
    --enable-gd \
    --with-freetype \
    --disable-rpath \
    --enable-inline-optimization \
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
    --enable-sysvmsg \
    --with-xmlrpc \
    --with-pear

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

cd ./bin

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

echo Will install apcu...
yes '' | ./pecl install apcu
yes '' | ./pecl install apcu_bc-beta

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

    INIT_SCRIPT="/etc/init.d/php7.4-fpm"

    # update init script
    if [ -e "$INIT_SCRIPT" ]; then
        "$INIT_SCRIPT" stop
        rm "$INIT_SCRIPT"
    fi

    ln -s /opt/php-build-config/php7_4-fpm "$INIT_SCRIPT"

    cd lib/
    ln -s ../../../php-build-config/php7.ini php.ini
    cd ..
    cd ..

    rm current-live
    ln -s ${DIRECTORY_TARGET} current-live

    "$INIT_SCRIPT" start

fi
