# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM php:7.3-apache-buster

RUN apt-get update && apt-get install -y \
        zlib1g-dev \
        libzip-dev \
        libxml2-dev \
        libssl-dev \
        libldap2-dev \
        libsasl2-dev \
        libgmp-dev \
        unzip \
        libyaml-dev \
        libaio1 \
        --no-install-recommends \
    && ln -fs /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/ \
    && ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
    && docker-php-ext-configure ldap --with-ldap-sasl --with-ldap \
    && docker-php-ext-install zip mysqli pdo_mysql gmp ldap \
    && pecl install apcu \
    && pecl install apcu_bc \
    && pecl install grpc \
    && pecl install yaml \
    && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apc.ini \
    && docker-php-ext-enable grpc \
    && docker-php-ext-enable yaml \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o \
    APT::AutoRemove::SuggestsImportant=false

# install NewRelic PHP agent
RUN apt-get update && apt-get install -y wget gnupg2 \
    && echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | tee /etc/apt/sources.list.d/newrelic.list \
    && wget -O- https://download.newrelic.com/548C16BF.gpg | apt-key add - \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y install newrelic-php5 \
    && newrelic-install install

# Apache set up
RUN a2enmod rewrite
COPY app/deploy/apache/identity-provider.conf /etc/apache2/sites-available
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY app/deploy/apache/php-custom.ini $PHP_INI_DIR/conf.d/
RUN a2dissite 000-default.conf default-ssl.conf
# disable access log, we are going to have it on ingress
RUN a2disconf other-vhosts-access-log
RUN a2ensite identity-provider.conf

# Identity Provider setup
ARG COMPOSER_AUTH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install
RUN app/deploy/prepare.sh
ARG DEV=false
RUN if [ "$DEV" != "true" ] ; then app/deploy/cleanup.sh ; fi
RUN set -ex \
    && . "$APACHE_ENVVARS" \
    && chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" /var/www/html/var

EXPOSE 80
CMD ["apache2-foreground"]
