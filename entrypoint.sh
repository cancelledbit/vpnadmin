#!/bin/bash
if [[ -z "$APPNAME" ]]; then
    sed -E "s/APPNAME/VPNadmin/g" /www/vpnadmin/env.tpl > /www/vpnadmin/.env
else
    sed -E "s/APPNAME/$APPNAME/g" /www/vpnadmin/env.tpl > /www/vpnadmin/.env
fi
if [[  -z "$IPSEC_KEY" ]]; then
    echo "%any %any : PSK "DefaultKey"" > /etc/ipsec.secrets
else
    echo "%any %any : PSK "$IPSEC_KEY"" > /etc/ipsec.secrets
fi


service nginx start && service php7.2-fpm start && tail -f /var/log/nginx/access.log