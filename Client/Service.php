<?php

namespace Ding\Client;

use Ding\Client\Client;
use Ding\Client\Text;

class Service
{
    const CHAT_DEFAULT = "chatfbc8ac81651d261a895557ff37dd5f24";//新建群ID，群名称：温都金服系统消息自动通知群
    const USER_DEFAULT = "411143392461";//默认发送消息用户id，姓名：史阳

    public $corpid;
    public $corpsecret;
    public $agentid;

    public function __construct(array $config)
    {
        if (isset($config['corpid']) && isset($config['corpsecret']) && isset($config['agentid'])) {
            $this->agentid = $config['agentid'];
            $this->corpsecret = $config['corpsecret'];
            $this->corpid = $config['corpid'];
        } else {
            throw new \Exception('配置文件缺少必要参数');
        }
    }

    //钉钉向群发送消息服务
    public function charSentText($content)
    {
        $client = new Client(['corpid' => $this->corpid, 'corpsecret' => $this->corpsecret, 'agentid' => $this->agentid]);
        $text = new Text($content);
        $client->initNew()->chatSend(Service::CHAT_DEFAULT, Service::USER_DEFAULT, $text);
    }
}
