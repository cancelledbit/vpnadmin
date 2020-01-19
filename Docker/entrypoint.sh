#!/bin/bash
function make_user () {
    echo "Using LOGIN:$1 PASSWORD:$2"
    cd /www/vpnadmin && echo "\$user = new App\User();\
            \$user->password = Hash::make('$2');\
            \$user->email = '$1';\
            \$user->name = 'Admin';\
            \$user->role = 'admin';\
            \$user->save();\
            exit" | php artisan tinker
}
if [[ -z "$APPNAME" ]]; then
    sed -E "s/APPNAME/VPNadmin/g" /www/vpnadmin/env.tpl > /www/vpnadmin/.env
else
    sed -E "s/APPNAME/$APPNAME/g" /www/vpnadmin/env.tpl > /www/vpnadmin/.env
fi
if [[  -z "$IPSEC_KEY" ]]; then
    echo "%any %any : PSK \"DefaultKey\"" > /etc/ipsec.secrets
else
    echo "%any %any : PSK \"$IPSEC_KEY\"" > /etc/ipsec.secrets
fi
if [[ -z "$LOGIN" ]]; then
    lp=($(echo $LOGIN | tr ":" "\n"))
        if [[ ${#lp[*]} -gt 1 ]]; then
            make_user ${lp[0]} ${lp[1]}
        else
            echo "No valid login:password pair provided, fallback to default \n"
            pwd=$(date +%s | md5sum | base64 | head -c 10)
            echo "generated PASSWORD: $pwd"
            make_user "admin@vpnadmin.local" $pwd
        fi
else
    echo "Using default login and password \n"
    pwd=$(date +%s | md5sum | base64 | head -c 10)
    echo "generated PASSWORD: $pwd"
    make_user "admin@vpnadmin.local" $pwd
fi
if [[ -z "$PUBLIC_IP" ]]; then
	PUBLIC_IP=$(wget -t 3 -T 15 -qO- http://ipv4.icanhazip.com)
fi



L2TP_NET='10.100.100.0/24'
L2TP_LOCAL='10.100.100.1'
L2TP_POOL='10.100.100.10-10.100.100.254'
PPTP_NET='10.100.101.0/24'
PPTP_LOCAL='10.100.101.1'
PPTP_POOL='10.100.101.10-10.100.101.254'
DNS_SRV1='8.8.8.8'
DNS_SRV2='8.8.4.4'
DNS_SRVS="\"$DNS_SRV1 $DNS_SRV2\""
SHA2_TRUNCBUG='no'


# Create IPsec (Libreswan) config
cat > /etc/ipsec.conf <<EOF
version 2.0
config setup
  virtual-private=%v4:192.168.0.0/16,%v4:172.16.0.0/12,%v4:!$L2TP_NET,%v4:!$XAUTH_NET
  protostack=netkey
  interfaces=%defaultroute
  uniqueids=no
conn shared
  left=%defaultroute
  leftid=$PUBLIC_IP
  right=%any
  encapsulation=yes
  authby=secret
  pfs=no
  rekey=no
  keyingtries=5
  dpddelay=30
  dpdtimeout=120
  dpdaction=clear
  ikev2=never
  ike=aes256-sha2,aes128-sha2,aes256-sha1,aes128-sha1,aes256-sha2;modp1024,aes128-sha1;modp1024
  phase2alg=aes_gcm-null,aes128-sha1,aes256-sha1,aes256-sha2_512,aes128-sha2,aes256-sha2
  sha2-truncbug=$SHA2_TRUNCBUG
conn l2tp-psk
  auto=add
  leftprotoport=17/1701
  rightprotoport=17/%any
  type=transport
  phase2=esp
  also=shared
EOF

if uname -r | grep -qi 'coreos'; then
  sed -i '/phase2alg/s/,aes256-sha2_512//' /etc/ipsec.conf
fi

# Create xl2tpd config
cat > /etc/xl2tpd/xl2tpd.conf <<EOF
[global]
port = 1701
[lns default]
ip range = $L2TP_POOL
local ip = $L2TP_LOCAL
require chap = yes
refuse pap = yes
require authentication = yes
name = l2tpd
pppoptfile = /etc/ppp/options.xl2tpd
length bit = yes
EOF

# Set xl2tpd options
cat > /etc/ppp/options.xl2tpd <<EOF
+mschap-v2
ipcp-accept-local
ipcp-accept-remote
noccp
auth
mtu 1280
mru 1280
proxyarp
lcp-echo-failure 4
lcp-echo-interval 30
connect-delay 5000
ms-dns $DNS_SRV1
EOF

cat > /etc/ppp/options.pptpd <<EOF
name pptpd
refuse-pap
refuse-chap
refuse-mschap
require-mschap-v2
require-mppe-128
ms-dns $DNS_SRV1
proxyarp
lock
nobsdcomp
novj
novjccomp
nologfd
EOF

cat > /etp/pptpd.conf <<EOF
option /etc/ppp/options.pptpd
logwtmp
localip $PPTP_LOCAL
remoteip $PPTP_POOL
EOF
if [ -z "$VPN_DNS_SRV1" ] || [ -n "$VPN_DNS_SRV2" ]; then
cat >> /etc/ppp/options.xl2tpd <<EOF
ms-dns $DNS_SRV2
EOF
fi

# Update sysctl settings
SYST='/sbin/sysctl -e -q -w'
if [ "$(getconf LONG_BIT)" = "64" ]; then
  SHM_MAX=68719476736
  SHM_ALL=4294967296
else
  SHM_MAX=4294967295
  SHM_ALL=268435456
fi
$SYST kernel.msgmnb=65536
$SYST kernel.msgmax=65536
$SYST kernel.shmmax=$SHM_MAX
$SYST kernel.shmall=$SHM_ALL
$SYST net.ipv4.ip_forward=1
$SYST net.ipv4.conf.all.accept_source_route=0
$SYST net.ipv4.conf.all.accept_redirects=0
$SYST net.ipv4.conf.all.send_redirects=0
$SYST net.ipv4.conf.all.rp_filter=0
$SYST net.ipv4.conf.default.accept_source_route=0
$SYST net.ipv4.conf.default.accept_redirects=0
$SYST net.ipv4.conf.default.send_redirects=0
$SYST net.ipv4.conf.default.rp_filter=0
$SYST net.ipv4.conf.eth0.send_redirects=0
$SYST net.ipv4.conf.eth0.rp_filter=0

# Create IPTables rules
iptables -I INPUT 1 -p udp --dport 1701 -m policy --dir in --pol none -j DROP
iptables -I INPUT 2 -m conntrack --ctstate INVALID -j DROP
iptables -I INPUT 3 -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -I INPUT 4 -p udp -m multiport --dports 500,4500 -j ACCEPT
iptables -I INPUT 5 -p udp --dport 1701 -m policy --dir in --pol ipsec -j ACCEPT
iptables -I INPUT 6 -p udp --dport 1701 -j DROP
iptables -I FORWARD 1 -m conntrack --ctstate INVALID -j DROP
iptables -I FORWARD 2 -i eth+ -o ppp+ -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -I FORWARD 3 -i ppp+ -o eth+ -j ACCEPT
iptables -I FORWARD 4 -i ppp+ -o ppp+ -s "$L2TP_NET" -d "$L2TP_NET" -j ACCEPT
iptables -I FORWARD 4 -i ppp+ -o ppp+ -s "$PPTP_NET" -d "$PPTP_NET" -j ACCEPT
iptables -A FORWARD -j DROP
iptables -t nat -I POSTROUTING -s "$PPTP_POOL" -o eth+ -j MASQUERADE
iptables -t nat -I POSTROUTING -s "$L2TP_NET" -o eth+ -j MASQUERADE

# Update file attributes
chmod 600 /etc/ipsec.secrets /etc/ppp/chap-secrets /etc/ipsec.d/passwd

# enable IP forwarding
sysctl -w net.ipv4.ip_forward=1
modprobe af_key
# configure firewall
iptables -t nat -A POSTROUTING -s 172.16.10.0/24 ! -d 172.16.10.0/24 -j MASQUERADE
iptables -t nat -A POSTROUTING -s 10.168.1.0/24 ! -d 10.168.1.0/24 -j MASQUERADE
iptables -A FORWARD -s 172.16.10.0/24 -p tcp -m tcp --tcp-flags FIN,SYN,RST,ACK SYN -j TCPMSS --set-mss 1356
iptables -A FORWARD -s 10.168.1.0/24 -p tcp -m tcp --tcp-flags FIN,SYN,RST,ACK SYN -j TCPMSS --set-mss 1356
iptables -A INPUT -i ppp+ -j ACCEPT
iptables -A OUTPUT -o ppp+ -j ACCEPT
iptables -A FORWARD -i ppp+ -j ACCEPT
iptables -A FORWARD -o ppp+ -j ACCEPT


service nginx start \
&& service php7.2-fpm start \
&& service pptpd start \
&& service rsyslog start \
&& mkdir -p /run/pluto /var/run/pluto /var/run/xl2tpd \
&& rm -f /run/pluto/pluto.pid /var/run/pluto/pluto.pid /var/run/xl2tpd.pid \
&& /usr/local/sbin/ipsec start \
&& exec /usr/sbin/xl2tpd -D -c /etc/xl2tpd/xl2tpd.conf \
&& tail -f /var/log/nginx/access.log