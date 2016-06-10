FROM ubuntu:14.04
MAINTAINER Hue Vuong <vuonghm@mail.nih.gov>

RUN apt-get update && apt-get install git curl apache2 php5 php5-json php5-memcached php5-xdebug php5-cli -y
RUN apt-get install php5-dev make php-pear -y
RUN echo "extension=stem.so" | tee -a /etc/php5/cli/php.ini

CMD ["/usr/sbin/apache2", "-D",  "FOREGROUND"]
ADD . /code
