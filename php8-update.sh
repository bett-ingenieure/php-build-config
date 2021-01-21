#!/bin/sh

# go to "php-8": the working directory
cd ..
if [ ! -e "php-8" ]
then
  mkdir "php-8"
fi
cd php-8

### WORKFLOW: UPDATE DEPENDENCIES ###
read -p "Update build package dependencies? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

  apt-get update
  apt-get update
  # Copied from PHP7 + PHP7.4
  apt-get install g++ libzip-dev autoconf libfcgi-dev libfcgi0ldbl libjpeg62-turbo-dev libmcrypt-dev libssl-dev libc-client2007e libc-client2007e-dev libxml2-dev libbz2-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libfreetype6-dev libkrb5-dev libpq-dev libxml2-dev libxslt1-dev libmagickwand-dev libsqlite3-dev libonig-dev libwebp-dev libxpm-dev
  apt-get install git
  ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a
fi

### WORKFLOW: ASK VERSION ###
VERSION=
while [ -z $VERSION ]
do
    echo -n 'Version? f.e. 8.0.0 '
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
  --prefix=/opt/php-8/php-${VERSION}/ \
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
  --enable-sysvmsg

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

  mkdir ../src
  mkdir ../src/imagick
  cd ../src/imagick/
  git clone https://github.com/Imagick/imagick .
  ../../bin/phpize
  ./configure --with-php-config=../../bin/php-config
  make
  make install

  cd ../../bin


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
  sleep 2s
  ./pecl install apcu

  ###
  ### // TODO Not available for PHP8 (21.01.2021) - needed?
  ### ./pecl install apcu_bc-beta
  ###

  echo Will install redis...
  sleep 2s
  ./pecl install redis

  cd ..
  cd ..

fi

read -p "No errors? Should activate? (y/n)?" RESPONSE
if [ "$RESPONSE" = "y" ]; then

  INIT_SCRIPT="/etc/init.d/php8-fpm"

  # update init script
  if [ -e "$INIT_SCRIPT" ]
  then
    "$INIT_SCRIPT" stop
    rm "$INIT_SCRIPT"
  fi

  ln -s /opt/php-build-config/php8-fpm "$INIT_SCRIPT"
  chmod +x /opt/php-build-config/php8-fpm

  cd php-${VERSION}/lib/
  ln -s ../../../php-build-config/php8.ini php.ini
  cd ..
  cd ..

  rm current-live
  ln -s php-${VERSION} current-live

  "$INIT_SCRIPT" start
  
fi
