FROM ubuntu:14.04

RUN apt-get update && apt-get install php5 php5-json php5-memcached php5-xdebug php5-cli -y
RUN apt-get install php5-dev make php-pear -y
RUN apt-get install git curl apache2 php5 libapache2-mod-php5 php5-mcrypt php5-mysql -y
RUN echo "extension=stem.so" | tee -a /etc/php5/cli/php.ini

# Install app
RUN rm -rf /var/www/*
ADD src /var/www

##Configure apache
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

EXPOSE 80

CMD ["/usr/sbin/apache2", "-D",  "FOREGROUND"]

ADD . /code
