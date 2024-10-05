FROM madpeter/phpapachepreload:php82

MAINTAINER Madpeter

COPY --chown=www-data:www-data . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/website

RUN apt-get update \
    && apt-get clean

ENV AnsSalt='notLoaded' \
    AnsRelay_1='https://google.com'