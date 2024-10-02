FROM madpeter/phpapachepreload:php82

MAINTAINER Madpeter

COPY --chown=www-data:www-data . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/website

# Install Composer and make vendor
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev

RUN apt-get update \
    && apt-get clean

ENV AnsSalt='notLoaded' \
    AnsRelay_1='https://google.com'