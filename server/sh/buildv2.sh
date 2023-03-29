#!/bin/bash
#检测当前用户是否为root用户
# shellcheck disable=SC2046
[ $(id -u) != "0" ]&& echo "error,not root user" && exit 1

read -p "是否开始安装依赖(y/n):" cho
case $cho in
#匹配y/n/*选项
y)
yum install -y readline-devel curl-devel gcc gcc-c++ python-devel yajl-devel lua-devel luarocks  cronolog
luarocks install luasocket
cd /usr/local && mkdir apr apr-util pcre apache libxml2
echo "依赖安装完成"
;;
n)
exit 1
;;
*)
echo "错误退出，请输入正确指令（y/n）"
exit 1
;;
esac

read -p "是否开始创建相关安装目录(y/n):" cho
case $cho in
#匹配y/n/*选项
y)
cd /usr/local && mkdir apr apr-util pcre apache libxml2
echo "创建相关安装目录成功"
;;
n)
exit 1
;;
*)
echo "错误退出，请输入正确指令（y/n）"
exit 1
;;
esac

read -p "是否开始安装apr apr-util pcre...(y/n):" cho
case $cho in
#匹配y/n/*选项
y)
cp /www/wwwroot/DRUST-WAF-Community/server/Extend/modsecurity/* /usr/local
echo "开始安装apr..."
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf apr-1.5.2.tar.gz
# shellcheck disable=SC2164
cd  apr-1.5.2
./configure --prefix=/usr/local/apr
make && make install

echo "安装apr成功"

echo "开始安装apr-util..."
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf apr-util-1.5.4.tar.gz
# shellcheck disable=SC2164
cd apr-util-1.5.4
./configure --prefix=/usr/local/apr-util -with-apr=/usr/local/apr/bin/apr-1-config
make && make install

echo "安装apr-util成功"

echo "开始安装pcre..."
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf pcre-8.43.tar.gz
# shellcheck disable=SC2164
cd pcre-8.43
./configure --prefix=/usr/local/pcre
make && make install

echo "安装pcre成功"

echo "开始安装libxml2..."
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf libxml2-2.9.9.tar.gz
# shellcheck disable=SC2164
cd libxml2-2.9.9
./configure --prefix=/usr/local/libxml2
make && make install

echo "安装libxml2成功"

echo "开始安装httpd..."
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf httpd-2.4.41.tar.gz
# shellcheck disable=SC2164
cd httpd-2.4.41
./configure --prefix=/usr/local/apache --with-apr=/usr/local/apr --with-apr-util=/usr/local/apr-util/ --with-pcre=/usr/local/pcre
make && make install

echo "安装httpd成功"

echo "开始安装modsecurity..."
# shellcheck disable=SC2164
cd /usr/lib64/
ln -s libexpat.so.1.6.0 libexpat.so
# shellcheck disable=SC2164
cd /usr/local
tar -zxvf modsecurity-2.9.3.tar.gz
# shellcheck disable=SC2164
cd modsecurity-2.9.3
./configure
make && make install

echo "安装modsecurity成功"



echo "开始创建相关文件..."
cd /usr/local && unzip -o owasp-modsecurity-crs-3.3-dev.zip
mkdir -p /usr/local/apache/conf/modsecurity/rules/base && mkdir -p /usr/local/apache/conf/vhost
mkdir /var/log/modsecurity && chmod 777 /var/log/modsecurity
mkdir /var/log/mlogc && chmod 777 /var/log/mlogc
\cp -rf /usr/local/modsecurity-2.9.3/modsecurity.conf-recommended /usr/local/apache/conf/modsecurity/modsecurity.conf
\cp -rf /usr/local/modsecurity-2.9.3/unicode.mapping /usr/local/apache/conf/modsecurity/unicode.mapping
\cp -rf /usr/local/owasp-modsecurity-crs-3.3-dev/crs-setup.conf.example /usr/local/apache/conf/modsecurity/crs-setup.conf
\cp -rf /usr/local/owasp-modsecurity-crs-3.3-dev/rules/* /usr/local/apache/conf/modsecurity/rules/base
mv -f /usr/local/apache/conf/modsecurity/rules/base/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf.example /usr/local/apache/conf/modsecurity/rules/base/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf && mv /usr/local/apache/conf/modsecurity/rules/base/RESPONSE-999-EXCLUSION-RULES-AFTER-CRS.conf.example /usr/local/apache/conf/modsecurity/rules/base/RESPONSE-999-EXCLUSION-RULES-AFTER-CRS.conf
touch /usr/local/apache/conf/modsecurity/rules/REQUEST-99999-CUSTOMIZE-RULES.conf && chmod 664 /usr/local/apache/conf/modsecurity/rules/REQUEST-99999-CUSTOMIZE-RULES.conf
touch /usr/local/apache/conf/modsecurity/rules/REQUEST-88888-GLOBAL-RULES.conf && chmod 664 /usr/local/apache/conf/modsecurity/rules/REQUEST-88888-GLOBAL-RULES.conf
touch /usr/local/apache/conf/modsecurity/rules/update-action.conf && chmod 664 /usr/local/apache/conf/modsecurity/rules/update-action.conf
touch /usr/local/apache/conf/modsecurity/rules/white-rule.conf && chmod 664 /usr/local/apache/conf/modsecurity/rules/white-rule.conf
cd /usr/local/apache/conf/modsecurity/rules/base && touch ip.allow.data ip.deny.data && chmod 664 ip.allow.data ip.deny.data
#\cp -rf /usr/local/modsecurity-2.9.3/mlogc/mlogc-default.conf /usr/local/modsecurity/bin/mlogc-default.conf

#\cp -rf /www/wwwroot/DRUST-WAF-Community/server/Extend/mlogc-default.conf /usr/local/modsecurity/bin/mlogc-default.conf
\cp -rf /www/wwwroot/DRUST-WAF-Community/server/Extend/crs-setup.conf /usr/local/apache/conf/modsecurity/crs-setup.conf
\cp -rf /www/wwwroot/DRUST-WAF-Community/server/Extend/modsecurity.conf /usr/local/apache/conf/modsecurity/modsecurity.conf
\cp -rf /www/wwwroot/DRUST-WAF-Community/server/Extend/httpd.conf /usr/local/apache/conf/httpd.conf

echo -e "SecRule REMOTE_ADDR \"@ipMatchFromFile ip.allow.data\" \"id:590001,phase:1,pass,nolog,ctl:ruleEngine=Off\"\nSecRule REMOTE_ADDR \"@ipMatchFromFile ip.deny.data\" \"id:590002,phase:1,nolog,auditlog,drop,msg:'black'\"" >> /usr/local/apache/conf/modsecurity/rules/base/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf
sed -i 's/80/10088/g' /www/server/nginx/conf/nginx.conf.default
sed -i 's/80/10088/g' /www/server/panel/vhost/nginx/phpfpm_status.conf
sed -i 's/80/10088/g' /www/server/panel/vhost/nginx/0.default.conf
echo "创建相关文件成功"
;;
n)
exit 1
;;
*)
echo "错误退出，请输入正确指令（y/n）"
exit 1
;;
esac

