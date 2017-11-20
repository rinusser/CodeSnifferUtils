FROM php:7.1-cli
WORKDIR /phpcs

RUN apt-get update && apt-get -y install wget unzip
RUN wget https://getcomposer.org/download/1.5.2/composer.phar

ADD phpconf.d/* /usr/local/etc/php/conf.d/

ADD composer.* /phpcs/
RUN php composer.phar install
RUN vendor/bin/phpcs --config-set default_standard RN && \
 vendor/bin/phpcs --config-set colors 1

ADD src /phpcs/src
RUN vendor/bin/phpcs --config-set installed_paths /phpcs/src/Standards

ADD phpunit.xml /phpcs/
ADD tests /phpcs/tests

WORKDIR /app
ENTRYPOINT ["/phpcs/vendor/bin/phpcs"]
