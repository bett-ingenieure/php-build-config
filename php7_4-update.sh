#!/bin/sh

# go to "php-7": the working directory
cd ..
if [ ! -e "php-7.4" ]
then
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
VERSION=
while [ -z $VERSION ]
do
    echo -n 'Version? f.e. 7.4.1 '
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
  --prefix=/opt/php-7.4/php-${VERSION}/ \
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

  INIT_SCRIPT="/etc/init.d/php7.4-fpm"

  # update init script
  if [ -e "$INIT_SCRIPT" ]
  then
    "$INIT_SCRIPT" stop
    rm "$INIT_SCRIPT"
  fi

  ln -s /opt/php-build-config/php7_4-fpm "$INIT_SCRIPT"

  cd php-${VERSION}/lib/
  ln -s ../../../php-build-config/php7.ini php.ini
  cd ..
  cd ..

  rm current-live
  ln -s php-${VERSION} current-live

  "$INIT_SCRIPT" start
  
fi
