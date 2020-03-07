#!/bin/sh

# go to "php-7": the working directory
cd ..
if [ ! -e "php-7" ]
then
  mkdir "php-7"
fi
cd php-7

### WORKFLOW: UPDATE DEPENDENCIES ###
read -p "Update build package dependencies? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

  apt-get update
  apt-get install g++ libzip-dev autoconf libfcgi-dev libfcgi0ldbl libjpeg62-turbo-dev libmcrypt-dev libssl-dev libc-client2007e libc-client2007e-dev libxml2-dev libbz2-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libfreetype6-dev libkrb5-dev libpq-dev libxml2-dev libxslt1-dev libmagickwand-dev
  ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a
fi

### WORKFLOW: ASK VERSION ###
VERSION=
while [ -z $VERSION ]
do
    echo -n 'Version? f.e. 7.1.5 '
    read VERSION
done

### WORKFLOW: FETCH ###
read -p "Fetch now? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

  if [ -e "mirror" ]
  then
    rm mirror
  fi
  
  wget http://de2.php.net/get/php-${VERSION}.tar.gz/from/this/mirror
  mv mirror php-${VERSION}.tar.gz
  tar xfvz php-${VERSION}.tar.gz
  chown -R root:root php-${VERSION}

  rm php-${VERSION}.tar.gz
fi

### WORKFLOW: CONFIGURE ###
read -p "Configure now? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then
  cd ./php-${VERSION}

./configure \
  --prefix=/opt/php-7/php-${VERSION}/ \
  --with-zlib-dir \
  --enable-mbstring \
  --with-libxml-dir=/usr \
  --enable-soap \
  --enable-calendar \
  --with-curl \
  --with-mcrypt \
  --with-gd \
  --disable-rpath \
  --enable-inline-optimization \
  --with-bz2 \
  --with-zlib \
  --enable-sockets \
  --enable-sysvsem \
  --enable-sysvshm \
  --enable-pcntl \
  --enable-mbregex \
  --with-mhash \
  --enable-zip \
  --with-pcre-regex \
  --with-pdo-mysql \
  --with-mysqli \
  --with-jpeg-dir=/usr/include/ \
  --with-xpm-dir=/usr/include/ \
  --with-png-dir=/usr \
  --with-webp-dir=/usr/include/ \
  --enable-gd-native-ttf \
  --with-openssl \
  --with-fpm-user=www-data \
  --with-fpm-group=www-data \
  --with-libdir=/lib/x86_64-linux-gnu \
  --enable-ftp \
  --with-imap \
  --with-imap-ssl \
  --with-kerberos \
  --with-gettext \
  --enable-fpm \
  --enable-intl \
  --enable-bcmath \
  --enable-exif \
  --enable-shmop \
  --enable-sysvmsg \
  --enable-wddx \
  --with-xmlrpc

  cd ..
fi

### WORKFLOW: MAKE ###
read -p "make now? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then
  cd ./php-${VERSION}

  make -j 4

  cd ..
fi

### WORKFLOW: INSTALL ###
read -p "make install now? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then
  cd ./php-${VERSION}

  make install

  cd ..
fi

### WORKFLOW: EXTENSIONS ###
read -p "should install extensions? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then
  cd ./php-${VERSION}/bin
  
  ./pecl -C ./pear.conf update-channels

  echo Will install imagick...
  sleep 2s
  ./pecl install imagick

  echo Will install apcu...
  sleep 2s
  ./pecl install apcu
  ./pecl install apcu_bc-beta

  echo Will install redis...
  sleep 2s
  ./pecl install redis

  cd ..
  cd ..

fi

read -p "No errors? Should activate? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

  INIT_SCRIPT="/etc/init.d/php7-fpm"

  # update init script
  if [ -e "$INIT_SCRIPT" ]
  then
    "$INIT_SCRIPT" stop
    rm "$INIT_SCRIPT"
  fi

  ln -s /opt/php-build-config/php7-fpm "$INIT_SCRIPT"

  cd php-${VERSION}/lib/
  ln -s ../../../php-build-config/php7.ini php.ini
  cd ..
  cd ..

  rm current-live
  ln -s php-${VERSION} current-live

  "$INIT_SCRIPT" start
  
fi
