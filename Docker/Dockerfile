FROM ubuntu:18.04
ENV TZ=Europe/Moscow
ENV SWAN_VER 3.27

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get -y install --no-install-recommends --no-upgrade php php-fpm php-xml \
         php-mysql php-mbstring nginx sudo git pptpd curl xl2tpd composer sqlite3 php-sqlite3 unzip php-zip \
         wget dnsutils openssl ca-certificates kmod \
         gawk grep sed net-tools iptables \
         bsdmainutils libunbound2 libcurl3-nss \
         libnss3-tools libevent-dev libcap-ng0 xl2tpd \
         libnss3-dev libnspr4-dev pkg-config libpam0g-dev libldns-dev \
         libcap-ng-dev libcap-ng-utils libselinux1-dev \
         libcurl4-nss-dev libsystemd-dev flex bison gcc make \
         libunbound-dev xmlto \
    && wget -t 3 -T 30 -nv -O "libreswan.tar.gz" "https://download.libreswan.org/libreswan-${SWAN_VER}.tar.gz" \
    || wget -t 3 -T 30 -nv -O "libreswan.tar.gz" "https://github.com/libreswan/libreswan/archive/v${SWAN_VER}.tar.gz" \
    && tar xzf "libreswan.tar.gz" \
    && rm -f "libreswan.tar.gz" \
    && cd "libreswan-${SWAN_VER}" \
    && echo "WERROR_CFLAGS =" > Makefile.inc.local \
    && echo "USE_DNSSEC=false =" >> Makefile.inc.local \
    && make -s programs \
    && make -s install \
    && rm -rf "libreswan-${SWAN_VER}" \
    && apt-get -yqq remove \
         libnss3-dev libnspr4-dev pkg-config libpam0g-dev \
         libcap-ng-dev libcap-ng-utils libselinux1-dev \
         libcurl4-nss-dev libsystemd-dev flex bison gcc make \
         libunbound-dev xmlto perl-modules perl \
    && rm -rf /var/lib/apt/lists/* \
    && rm /etc/ipsec.secrets /etc/ppp/chap-secrets

RUN mkdir /www && chown -R www-data /www && cd /www
RUN curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
RUN apt install -y nodejs
WORKDIR /www
RUN git clone https://github.com/cancelledbit/vpnadmin.git
RUN chown -R www-data /www && touch /www/vpnadmin/chap_secrets.conf && chmod 777 /www/vpnadmin/chap_secrets.conf \
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
EXPOSE 4500
EXPOSE 500
EXPOSE 80
ENTRYPOINT ["bash","/www/entrypoint.sh"]
