<?php

namespace Ding\Client;

class Text
{
    public $text;

    public function __construct($content)
    {
        $this->msgtype = 'text';
        $this->text = [
            'content' => strval($content),
        ];
    }
}
