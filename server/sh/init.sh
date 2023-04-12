#!/bin/bash
#检测当前用户是否为root用户
# shellcheck disable=SC2046
[ $(id -u) != "0" ] && echo "error,not root user" && exit 1

read -p "请输入本机IP: " nIP
echo "本机IP为 $nIP"
read -p "请输入被替换IP(原IP): " yIP
echo "被替换IP(原IP)为 $yIP"

if [ "$nIP" == "$yIP" ]; then
    echo "错误退出，请输入正确指令（y/n）"
    exit 1
fi
read -p "是否开始替换IP(Y/n):" cho
cho=${cho:-y}

case $cho in
#匹配y/n/*选项
y)
    chmod -R 777 /www/wwwroot/DRUST-WAF-Community/server/storage
    #替换.env
    sed -i "s/$yIP/$nIP/g" /www/wwwroot/DRUST-WAF-Community/server/.env
    echo "替换/www/wwwroot/DRUST-WAF-Community/server/.env 完成"

    #重启队列
    /www/server/panel/pyenv/bin/supervisorctl restart mlogic:mlogic_00
    echo "重启消息队列完成"
    /etc/init.d/redis restart
    echo "重启redis完成"
    sed -i "s/$yIP/$nIP/g" /www/wwwroot/DRUST-WAF-Community/web/.env.production
    cd /www/wwwroot/DRUST-WAF-Community/web/ && npm run build && rm -rf /www/wwwroot/DRUST-WAF-Community/server/public/web && cp -r /www/wwwroot/DRUST-WAF-Community/web/dist /www/wwwroot/DRUST-WAF-Community/server/public/web
    echo "重新打包页面完成"
    #替换站点
    sed -i "0,/$yIP/s/$yIP/$nIP/" /www/server/panel/vhost/nginx/192.168.166.129.conf
    /etc/init.d/nginx restart
    echo "替换/www/server/panel/vhost/nginx/192.168.166.129.conf 完成"
    ;;
n)
    exit 1
    ;;
*)
    echo "错误退出，请输入正确指令（y/n）"
    exit 1
    ;;
esac
