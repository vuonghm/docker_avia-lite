FROM ubuntu:14.04

RUN apt-get update && apt-get install php5 php5-json php5-mysql php5-mongo php5-memcached php5-xdebug php5-cli -y
RUN apt-get install php5-dev make php-pear -y
RUN echo "extension=stem.so" | tee -a /etc/php5/cli/php.ini

ADD . /code
