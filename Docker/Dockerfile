FROM ubuntu:18.04
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get -y install --no-install-recommends --no-upgrade php php-fpm php-xml \
php-mysql php-mbstring nginx sudo git ca-certificates pptpd curl xl2tpd strongswan composer sqlite3 php-sqlite3 unzip php-zip
RUN mkdir /www && chown -R www-data /www && cd /www
RUN curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
RUN apt install -y nodejs
WORKDIR /www
RUN git clone https://github.com/cancelledbit/vpnadmin.git
RUN chown -R www-data /www && touch /www/vpnadmin/chap_secrets.conf && chmod 777 /www/vpnadmin/chap_secrets.conf \
    && rm /etc/ppp/chap-secrets \
    && ln -s /www/vpnadmin/chap_secrets.conf /etc/ppp/chap-secrets
RUN cd /www/vpnadmin && composer install && npm install && npm run dev
COPY nginx.tpl /etc/nginx/sites-available/default
COPY env.tpl /www/vpnadmin/env.tpl
#temporary env file to make migrations
COPY env.tpl /www/vpnadmin/.env
COPY pptpd.conf /etc/
COPY xl2tpd.conf /etc/xl2tpd/
COPY options.pptpd /etc/ppp/
COPY options.xl2tpd /etc/ppp/
COPY ipsec.conf /etc/
RUN touch vpnadmin.sqlite && chown www-data vpnadmin.sqlite
WORKDIR /www/vpnadmin
RUN php artisan migrate:fresh
COPY entrypoint.sh /www/
RUN chmod 777 /www/entrypoint.sh

WORKDIR /
EXPOSE 1701
EXPOSE 1723
EXPOSE 80
ENTRYPOINT ["bash","/www/entrypoint.sh"]
