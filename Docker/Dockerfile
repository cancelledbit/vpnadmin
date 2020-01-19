FROM debian:stretch
ENV TZ=Europe/Moscow
ENV SWAN_VER 3.29
ENV L2TP_VER 1.3.12

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get -y install --no-install-recommends --no-upgrade php php-fpm php-xml \
	php-mysql php-mbstring nginx sudo git pptpd curl xl2tpd composer sqlite3 php-sqlite3 unzip php-zip \
	wget dnsutils openssl ca-certificates kmod iproute gawk grep sed net-tools iptables bsdmainutils libcurl3-nss \
	libnss3-tools libevent-dev libcap-ng0 xl2tpd libnss3-dev libnspr4-dev pkg-config libpam0g-dev \
	libcap-ng-dev libcap-ng-utils libselinux1-dev libcurl4-nss-dev libpcap0.8-dev flex bison gcc make \
	&& cd /opt/src || mkdir /opt/src && cd /opt/src \
	&& wget -t 3 -T 30 -nv -O libreswan.tar.gz "https://github.com/libreswan/libreswan/archive/v${SWAN_VER}.tar.gz" \
	|| wget -t 3 -T 30 -nv -O libreswan.tar.gz "https://download.libreswan.org/libreswan-${SWAN_VER}.tar.gz" \
	&& tar xzf libreswan.tar.gz \
	&& rm -f libreswan.tar.gz \
	&& cd "libreswan-${SWAN_VER}" \
	&& printf 'WERROR_CFLAGS =\nUSE_DNSSEC = false\nUSE_DH31 = false\n' > Makefile.inc.local \
	&& printf 'USE_NSS_AVA_COPY = true\nUSE_NSS_IPSEC_PROFILE = false\n' >> Makefile.inc.local \
	&& printf 'USE_GLIBC_KERN_FLIP_HEADERS = true\nUSE_SYSTEMD_WATCHDOG = false\n' >> Makefile.inc.local \
	&& make -s base \
	&& make -s install-base \
	&& cd /opt/src \
	&& rm -rf "/opt/src/libreswan-${SWAN_VER}" \
	&& wget -t 3 -T 30 -nv -O xl2tpd.tar.gz "https://github.com/xelerance/xl2tpd/archive/v${L2TP_VER}.tar.gz" \
	|| wget -t 3 -T 30 -nv -O xl2tpd.tar.gz "https://debian.osuosl.org/debian/pool/main/x/xl2tpd/xl2tpd_${L2TP_VER}.orig.tar.gz" \
	&& tar xzf xl2tpd.tar.gz \
	&& rm -f xl2tpd.tar.gz \
	&& cd "xl2tpd-${L2TP_VER}" \
	&& make -s \
	&& PREFIX=/usr make -s install \
	&& cd /opt/src \
	&& rm -rf "/opt/src/xl2tpd-${L2TP_VER}" \
	&& apt-get -yqq remove \
		libnss3-dev libnspr4-dev pkg-config libpam0g-dev \
		libcap-ng-dev libcap-ng-utils libselinux1-dev \
		libcurl4-nss-dev libpcap0.8-dev flex bison gcc make \
		perl-modules perl \
	&& rm -rf /var/lib/apt/lists/*
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
RUN apt-get -yqq remove \
             libnss3-dev libnspr4-dev pkg-config libpam0g-dev \
             libcap-ng-dev libcap-ng-utils libselinux1-dev \
             libcurl4-nss-dev libsystemd-dev flex bison gcc make \
             libunbound-dev xmlto perl-modules perl \
    && apt-get -yqq autoremove \
    && apt-get -yqq clean

WORKDIR /
EXPOSE 1701
EXPOSE 1723
EXPOSE 4500
EXPOSE 500
EXPOSE 80
ENTRYPOINT ["bash","/www/entrypoint.sh"]
