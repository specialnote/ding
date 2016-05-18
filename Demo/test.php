<?php

require_once  __DIR__.'/../Client/Client.php';
require_once __DIR__.'/../Client/Text.php';
$client = new Client();
$text = new Text('测试程序向钉钉发送消息');
//史阳 向 温都金服系统消息自动通知群 发送消息
$client->chat_send('chatfbc8ac81651d261a895557ff37dd5f24', '411143392461', $text);
