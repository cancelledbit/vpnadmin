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
    echo "%any %any : PSK "DefaultKey"" > /etc/ipsec.secrets
else
    echo "%any %any : PSK "$IPSEC_KEY"" > /etc/ipsec.secrets
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

service nginx start && service php7.2-fpm start && tail -f /var/log/nginx/access.log