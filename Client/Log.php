<?php

namespace Ding\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log implements LoggerInterface
{
    public $log_path = __DIR__ . '/../Log/';

    private function addFileLog($message)
    {
        $file = $this->getLogFileName();
        $res = file_put_contents($file, $message);
        if ($res === false) {
            throw new \Exception('写入日志文件失败');
        }
    }

    private function handleMessage($level, $message, $context = [])
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        $new_message = strtr($message, $replace);

        $message = 'log time : ' . date('Y-m-d H:i:s') . '; log level : ' . $level . '; log message : ' . $new_message . PHP_EOL;
        return $message;
    }

    private function getLogFileName()
    {
        $path = $this->log_path;
        if (!file_exists($path)) {
            if (!mkdir($path)) {
                throw new \Exception('新建日志目录 ' . $path . ' 失败');
            }
        }
        chmod($path, 0755);
        if (!is_writable($path)) {
            throw new \Exception('日志目录 ' . $path . ' 没有“W”权限');
        }
        $file = rtrim($path, '/') . '/ding_log_' . date('Ym') . '.log';
        return $file;
    }

    public function emergency($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::EMERGENCY, $message, $context);
        $this->addFileLog($message);
    }

    public function alert($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::ALERT, $message, $context);
        $this->addFileLog($message);
    }

    public function critical($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::CRITICAL, $message, $context);
        $this->addFileLog($message);
    }

    public function error($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::ERROR, $message, $context);
        $this->addFileLog($message);
    }

    public function warning($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::WARNING, $message, $context);
        $this->addFileLog($message);
    }

    public function notice($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::NOTICE, $message, $context);
        $this->addFileLog($message);
    }

    public function info($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::INFO, $message, $context);
        $this->addFileLog($message);
    }

    public function debug($message, array $context = array())
    {
        $message = $this->handleMessage(LogLevel::DEBUG, $message, $context);
        $this->addFileLog($message);
    }

    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::EMERGENCY :
                $this->emergency($message);
                break;
            case LogLevel::ALERT :
                $this->alert($message);
                break;
            case LogLevel::CRITICAL :
                $this->critical($message);
                break;
            case LogLevel::ERROR :
                $this->error($message);
                break;
            case LogLevel::WARNING :
                $this->warning($message);
                break;
            case LogLevel::NOTICE :
                $this->notice($message);
                break;
            case LogLevel::INFO :
                $this->info($message);
                break;
            case LogLevel::DEBUG :
                $this->debug($message);
                break;
            default :
                break;
        }
    }
}