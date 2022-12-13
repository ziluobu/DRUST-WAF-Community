<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Web
 *
 * @property int $id
 * @property string|null $web_sysname
 * @property int $web_port 网站源端口
 * @property string $web_name 防护域名
 * @property string $source_ip 源站地址
 * @property string|null $proxy_name
 * @property int $dst_port 目的端口
 * @property int $is_https 是否开启https
 * @property string|null $proxy_catefile 证书文件
 * @property string|null $proxy_catekeyfile 密钥文件
 * @property string|null $proxy_catechainfile 证书链文件
 * @property int|null $protect_status 1防护模式 0转发模式
 * @property int|null $group_id
 * @property int|null $web_active 0未检测,1存活,2不可达
 * @property int|null $is_parse 0未解析1已解析
 * @property string|null $web_content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Web newModelQuery()
 * @method static Builder|Web newQuery()
 * @method static Builder|Web query()
 * @method static Builder|Web whereCreatedAt($value)
 * @method static Builder|Web whereDstPort($value)
 * @method static Builder|Web whereGroupId($value)
 * @method static Builder|Web whereId($value)
 * @method static Builder|Web whereIsHttps($value)
 * @method static Builder|Web whereIsParse($value)
 * @method static Builder|Web whereProtectStatus($value)
 * @method static Builder|Web whereProxyCatechainfile($value)
 * @method static Builder|Web whereProxyCatefile($value)
 * @method static Builder|Web whereProxyCatekeyfile($value)
 * @method static Builder|Web whereProxyName($value)
 * @method static Builder|Web whereSourceIp($value)
 * @method static Builder|Web whereUpdatedAt($value)
 * @method static Builder|Web whereWebActive($value)
 * @method static Builder|Web whereWebContent($value)
 * @method static Builder|Web whereWebName($value)
 * @method static Builder|Web whereWebPort($value)
 * @method static Builder|Web whereWebSysname($value)
 * @mixin \Eloquent
 */
class Web extends Model
{
    use HasFactory;

    protected $table = 'web';
    protected $primaryKey = 'id';

    protected $hidden = [
        'web_content'
    ];

    /**
     * 模型的 "booted" 方法
     *
     * @return void
     */
    protected static function booted()
    {
        $group_id = request()->input('user_group_id');
        if ($group_id > 0 && request()->input('user_id') != 1) {
            static::addGlobalScope('group_id', function (Builder $builder) use ($group_id) {
                $builder->where('group_id', $group_id);
            });
        }
        $redis = app('redis')->connection('host');
        static::saved(function ($web) use ($redis) {
            if ($web->web_port != 80) {
                $redis->set($web->web_name . ':' . $web->web_port, $web->id);
            } else {
                $redis->set($web->web_name, $web->id);
            }

            $server_name = $web->web_name;

            $cateFile    = storage_path('app/ssl' . '/' . $web->proxy_catefile);
            $cateKeyFile = storage_path('app/ssl' . '/' . $web->proxy_catekeyfile);;
            $cateChainFile = storage_path('app/ssl' . '/' . $web->proxy_catechainfile);;
            $port          = $web->web_port;
            $https         = $web->is_https;
            $SecRuleEngine = '';
            if ($web->protect_status == 0) {
                $SecRuleEngine = "SecRuleEngine Off";
            }
            if ($https == 0) {
                $proxy_name = "http://" . $web->source_ip . ':' . $web->dst_port . '/';
                #判断是否需要强制跳转到https
                $https_web = self::where('is_https', 1)
                    ->where('web_name', $web->web_name)
                    ->where('web_port', 443)
                    ->first();
                if ($https_web && $web->web_name !='admin.zz-volunteer.com') {
                    $str = <<<EOT
<VirtualHost *:$port>
        ServerName $server_name

        $SecRuleEngine
        ProxyPreserveHost On
        ProxyRequests Off

        # 强制HTTP跳转HTTPS
        RewriteEngine on
        RewriteCond   %{HTTPS} !=on
        RewriteRule   ^(.*)  https://%{SERVER_NAME}$1 [L,R]

        ErrorLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-error_log"
        CustomLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-access_log" common
</VirtualHost>
EOT;
                } else {
                    $str = <<<EOT
<VirtualHost *:$port>
        ServerName $server_name

        $SecRuleEngine
        ProxyPreserveHost On
        ProxyRequests Off

        ProxyPass / $proxy_name
        ProxyPassReverse / $proxy_name

        ErrorLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-error_log"
        CustomLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-access_log" common
</VirtualHost>
EOT;
                }

            } else {
                $proxy_name = "https://" . $web->source_ip . ':' . $web->dst_port . '/';
                $str        = <<<EOT
<VirtualHost *:$port>
    ServerName $server_name
    $SecRuleEngine
    SSLEngine on
    SSLProxyEngine On

    SSLCipherSuite EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5
    SSLProtocol All -SSLv2 -SSLv3 -TLSv1
    #errorDocument 404 /404.html
    SSLCertificateFile "$cateFile"
    SSLCertificateKeyFile "$cateKeyFile"
    SSLCertificateChainFile "$cateChainFile"
    ProxyRequests Off
    ProxyPreserveHost On
    ProxyPass / $proxy_name
    ProxyPassReverse / $proxy_name

    ErrorLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-error_log"
    CustomLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-access_log" common
</VirtualHost>
EOT;
            }
            $web->web_content = $str;
            $web->saveQuietly();
            if ($web->is_https && $web->web_port == 443 && $web->web_name !='admin.zz-volunteer.com') {
                #修改对应的80端口强制跳转
                $http_web = self::where('is_https', 0)
                    ->where('web_name', $web->web_name)
                    ->where('web_port', 80)
                    ->first();
                if ($http_web) {
                    $str                   = <<<EOT
<VirtualHost *:80>
        ServerName $http_web->web_name

        $SecRuleEngine
        ProxyPreserveHost On
        ProxyRequests Off

        # 强制HTTP跳转HTTPS
        RewriteEngine on
        RewriteCond   %{HTTPS} !=on
        RewriteRule   ^(.*)  https://%{SERVER_NAME}$1 [L,R]

        ErrorLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-error_log"
        CustomLog "| /usr/sbin/cronolog /home/logs/apache/%Y%m%d/$server_name-access_log" common
</VirtualHost>
EOT;
                    $http_web->web_content = $str;
                    $http_web->save();
                }
            }
            //拷贝文件
            $cmd = "";
            if ($https == 1 && env('IS_MASTER') && env('APP_ENV') == 'production') {
                $ips = config('api.scp_ip');
                if ($ips) {
                    $path = storage_path('app/ssl/');
                    foreach ($ips as $ip) {
                        $cmd = "sudo scp $cateFile $cateKeyFile $cateChainFile root@$ip:$path";
                        \Log::error($cmd);
                        $result = exec_system($cmd);
                        if ($result !== 0) {
                            throw new \Exception('文件同步失败=>' . $result);
                        }
                    }
                }
            }
        });
        static::deleted(function ($web) use ($redis) {
            if ($web->web_port != 80) {
                $key = $web->web_name . ':' . $web->web_port;
            } else {
                $key = $web->web_name;
            }
            $redis->del([$key]);
        });
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    //获取类型
    public static function type()
    {
        return self::pluck('web_sysname', 'web_id')->toArray();
    }
}
