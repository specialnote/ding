<?php

namespace Ding\Client;

use Exception;
use Ding\Client\Log;
use Ding\Client\Http;

class Client
{
    private $corpid;//企业Id
    private $corpsecret; //企业应用的凭证密钥
    private $agentid;//应用id

    public $access_token;//获取到的凭证

    private $access_token_url = 'https://oapi.dingtalk.com/gettoken';
    private $department_url = 'https://oapi.dingtalk.com/department/list';
    private $department_user_url = 'https://oapi.dingtalk.com/user/simplelist';
    private $company_message_send_url = 'https://oapi.dingtalk.com/message/send';
    private $chat_create_url = 'https://oapi.dingtalk.com/chat/create';
    private $chat_send_url = 'https://oapi.dingtalk.com/chat/send';

    const GET_ACCESS_TOKEN = 'getAccessToken';
    const GET_DEPARTMENT = 'getDepartment';
    const GET_DEPARTMENT_USER = 'getDepartmentUser';
    const COMPANY_MESSAGE_SEND = 'companyMessageSend';
    const CHAT_CREATE = 'chatCreate';
    const CHAT_SEND = 'chatSend';

    public function __construct($config)
    {
        if (isset($config['corpid']) && isset($config['corpsecret']) && isset($config['agentid'])) {
            $this->corpid = $config['corpid'];
            $this->corpsecret = $config['corpsecret'];
            $this->agentid = $config['agentid'];
        } else {
            (new Log())->error('配置参数错误');
            throw new Exception('配置参数错误');
        }
    }

    /**
     * 初始化，主要是初始化 access_token
     * @return $this
     * @throws Exception
     */
    public function initNew()
    {
        $json_file = __DIR__ . '/../Config/temp.json';
        $time = time();
        if (file_exists($json_file)) {
            $content = file_get_contents($json_file);
            $data = json_decode($content, true);
            if (isset($data['access_token']) && isset($data['expire_time'])) {
                $access_token = $data['access_token'];
                $expire_time = $data['expire_time'];
                if ($expire_time > $time) {
                    $this->access_token = $access_token;
                    return $this;
                }
            }
        }
        $access_token = $this->getAccessToken();
        $expire_time = $time + 7200 - 60 * 10;
        file_put_contents($access_token, json_encode(['access_token' => $access_token, 'expire_time' => $expire_time]));
        $this->access_token = $access_token;
        return $this;
    }

    /**
     * 获取AccessToken
     * @return string
     * @throws Exception
     */
    private function getAccessToken()
    {
        require_once __DIR__ . '/Http.php';
        $url = $this->access_token_url . '?corpid=' . $this->corpid . '&corpsecret=' . $this->corpsecret;
        $result = Http::curlGet($url);
        return $this->handleResult($result, Client::GET_ACCESS_TOKEN);
    }

    /**
     * 处理钉钉请求处理结果
     * @param string $result 请求钉钉得到的json串
     * @param string $name 调用处理结果的函数名称
     * @return mixed
     * @throws Exception
     */
    private function handleResult($result, $name)
    {
        $res = json_decode($result, true);
        //没有获取到AccessToken
        if (Client::GET_ACCESS_TOKEN === $name && 0 !== $res['errcode']) {
            (new Log())->alert($res["errmsg"]);
        }
        if (0 === $res['errcode']) {
            switch ($name) {
                case Client::GET_ACCESS_TOKEN :
                    return $res['access_token'];
                    break;
                case Client::GET_DEPARTMENT :
                    return $res['department'];
                    break;
                case Client::GET_DEPARTMENT_USER :
                    return $res['userlist'];
                    break;
                case Client::COMPANY_MESSAGE_SEND :
                    return true;
                    break;
                case Client::CHAT_CREATE :
                    return $res['chatid'];
                    break;
                case Client::CHAT_SEND :
                    return true;
                    break;
                default :
                    return false;
                    break;
            }
        } else {
            (new Log())->error($res["errmsg"]);
            throw new Exception($res["errmsg"]);
        }
    }

    /**
     * 获取部门列表
     * @return array
     * @throws Exception
     */
    public function getDepartment()
    {
        $url = $this->department_url . '?access_token=' . $this->access_token;
        $result = Http::curlGet($url);
        return $this->handleResult($result, Client::GET_DEPARTMENT);
    }

    /**
     * 获取部门用户
     * @param integer $department_id 部门id
     * @return array
     * @throws Exception
     */
    public function getDepartmentUser($department_id)
    {
        $url = $this->department_user_url . '?access_token=' . $this->access_token . '&department_id=' . intval($department_id);
        $result = Http::curlGet($url);
        return $this->handleResult($result, Client::GET_DEPARTMENT_USER);
    }

    /**
     * 企业发送消息
     * @param Text $text 消息对象
     * @param string $toUser 员工ID列表（消息接收者，多个接收者用' | '分隔）。特殊情况：指定为@all，则向该企业应用的全部成员发送员工ID列表（消息接收者，多个接收者用' | '分隔）。特殊情况：指定为@all，则向该企业应用的全部成员发送
     * @param string $toParty 部门id列表，多个接收者用' | '分隔。当touser为@all时忽略本参数 touser或者toparty 二者有一个必填
     * @return bool
     * @throws Exception
     */
    public function companyMessageSend(Text $text, $toUser, $toParty = '')
    {
        $url = $this->company_message_send_url . '?access_token=' . $this->access_token;
        $requestData = [
            'touser' => $toUser,
            'toparty' => $toParty,
            'agentid' => $this->agentid,
            'msgtype' => $text->msgtype,
            'text' => $text->text,
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        return $this->handleResult($result, Client::COMPANY_MESSAGE_SEND);
    }

    /**
     * 创建群
     * @param string $name 群名称
     * @param string $owner 群主userId，员工唯一标识ID；必须为该会话useridlist的成员之一
     * @param array $useridlist 群成员列表，每次最多操作40人，群人数上限为1000
     * @return string 返回群chatid
     * @throws Exception
     */
    public function chatCreate($name, $owner, array $useridlist)
    {
        $url = $this->chat_create_url . '?access_token=' . $this->access_token;
        $requestData = [
            'name' => strval($name),
            'owner' => strval($owner),
            'useridlist' => $useridlist
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        return $this->handleResult($result, Client::CHAT_CREATE);
    }

    /**
     * 向群里发文本消息
     * @param string $chatId 群会话的id
     * @param string $sender 发送者的userid
     * @param Text $text 消息对象
     * @return bool
     * @throws Exception
     */
    public function chatSend($chatId, $sender, Text $text)
    {
        $url = $this->chat_send_url . '?access_token=' . $this->access_token;
        $requestData = [
            'chatid' => strval($chatId),
            'sender' => strval($sender),
            'msgtype' => $text->msgtype,
            'text' => $text->text
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        return $this->handleResult($result, Client::CHAT_SEND);
    }
}
