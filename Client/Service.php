<?php

namespace Ding\Client;

use Ding\Client\Client;
use Ding\Client\Text;

class Service
{
    const CHAT_DEFAULT = "chatfbc8ac81651d261a895557ff37dd5f24";//新建群ID，群名称：温都金服系统消息自动通知群
    const USER_DEFAULT = "411143392461";//默认发送消息用户id，姓名：史阳

    //钉钉向群发送消息服务
    public static function charSentText($content)
    {
        $config = require_once __DIR__ . '/../Config/config.php';
        if (isset($config['corpid']) && isset($config['corpsecret']) && isset($config['agentid'])) {
            $client = new Client($config);
            $text = new Text($content);
            $client->initNew()->chatSend(self::CHAT_DEFAULT, self::USER_DEFAULT, $text);
        } else {
            throw new \Exception('配置文件缺少必要参数');
        }
    }
}
