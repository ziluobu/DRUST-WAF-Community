<?php

return [
    'graceful'        => env('GRACEFUL', '/usr/local/apache/bin/apachectl graceful'),
    'ip_allow'        => env('IP_ALLOW', '/usr/local/apache/conf/modsecurity/rules/base/ip.allow.data'),
    'ip_deny'         => env('IP_DENY', '/usr/local/apache/conf/modsecurity/rules/base/ip.deny.data'),
    'customize_rules' => env('CUSTOMIZE_RULES', '/usr/local/apache/conf/modsecurity/rules/REQUEST-99999-CUSTOMIZE-RULES.conf'),
    'global_rules'    => env('GLOBAL_RULES', '/usr/local/apache/conf/modsecurity/rules/REQUEST-88888-GLOBAL-RULES.conf'),
    'onlie_rules'     => env('ONLIE_RULE', '/usr/local/apache/conf/modsecurity/rules/DINGXIN-RULES.conf'),
    'update_action'   => env('UPDATE_ACTION', '/usr/local/apache/conf/modsecurity/rules/update-action.conf'),
    'white_rule'      => env('WHITE_RULE', '/usr/local/apache/conf/modsecurity/rules/white-rule.conf'),
    'vhost_path'      => env('VHOST_PATH', '/usr/local/apache/conf/vhost/'),
    'hfish_ip'        => env('HFISH_IP', '172.20.77.23'),
    'bt_key'          => env('BT_KEY'),
    'bt_host'         => env('BT_HOST', '127.0.0.1'),
    'syn_list_key'    => env('SYN_LIST_KEY', 'todoJobList'),
];
