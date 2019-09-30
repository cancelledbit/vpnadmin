FROM ubuntu:18.04
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get -y install --no-install-recommends --no-upgrade php php-fpm php-xml \
php-mysql php-mbstring npm nginx sudo git ca-certificates pptpd xl2tpd strongswan composer sqlite3 php-sqlite3 unzip php-zip
RUN mkdir /www && chown -R www-data /www && cd /www
WORKDIR /www
RUN git clone https://github.com/cancelledbit/vpnadmin.git
RUN chown -R www-data /www && touch /www/vpnadmin/chap-secrets.conf && chmod 777 /www/vpnadmin/chap-secrets.conf
RUN cd /www/vpnadmin && composer install && npm install && npm run dev
COPY ./nginx.tpl /etc/nginx/sites-available/default
COPY env.tpl /www/vpnadmin/env.tpl
#temporary env file to make migrations
COPY env.tpl /www/vpnadmin/.env
RUN touch vpnadmin.sqlite && chown www-data vpnadmin.sqlite
WORKDIR /www/vpnadmin
COPY ./entrypoint.sh /www/
RUN chmod 777 /www/entrypoint.sh
WORKDIR /
ENTRYPOINT ["bash","/www/entrypoint.sh"]
