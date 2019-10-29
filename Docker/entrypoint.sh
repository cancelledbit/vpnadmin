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
&& service ipsec start \
&& service xl2tpd start \
&& service pptpd start \
&& tail -f /var/log/nginx/access.log