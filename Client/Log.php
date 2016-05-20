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

    private function getLogFileName()
    {
        $path = $this->log_path;
        if (is_dir($path)) {
            if (!file_exists($path)) {
                if (!mkdir($path)) {
                    throw new \Exception('日志目录 ' . $path . ' 不存在');
                }
            }
            chmod($path, "u+rwx");
            if (!is_writable($path)) {
                throw new \Exception('日志目录 ' . $path . ' 不存在');
            }
            $file = rtrim($path, '/') . '/ding_log_' . date('Ym') . '.log';
            return $file;
        } else {
            throw new \Exception('日志目录 ' . $path . ' 不存在');
        }
    }

    private function interpolate($message, array $context = array())
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        return strtr($message, $replace);
    }

    public function emergency($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function alert($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function critical($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function error($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function warning($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function notice($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function info($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function debug($message, array $context = array())
    {
        $this->addFileLog($message);
    }

    public function log($level, $message, array $context = array())
    {
        $message = 'log time : '.date('Y-m-d H:i:s').'; log level : ' . $level . '; log message : ' . $this->interpolate($message, $context);
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