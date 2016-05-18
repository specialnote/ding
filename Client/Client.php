<?php

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

    public function __construct()
    {
        $config = require_once __DIR__ . '/../Config/config.php';
        if (isset($config['corpid']) && isset($config['corpsecret']) && isset($config['agentid'])) {
            $this->corpid = $config['corpid'];
            $this->corpsecret = $config['corpsecret'];
            $this->agentid = $config['agentid'];
            $time = time();
            if (isset($_SESSION['access_token']) && isset($_SESSION['access_token_time']) && $time > $_SESSION['access_token_time']) {
                $this->access_token = $_SESSION['access_token'];
            } else {
                $token = $this->getAccessToken();
                $this->access_token = $token;
                $_SESSION['access_token'] = $token;
                $_SESSION['access_token_time'] = $time + 7200;//token有效时间
            }
        } else {
            throw new Exception('配置文件缺少必要参数');
        }
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
        $res = json_decode($result, true);
        if (0 === $res["errcode"]) {
            return $res['access_token'];
        } else {
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
        $url = $this->department_url.'?access_token='.$this->access_token;
        $result = Http::curlGet($url);
        $res = json_decode($result, true);
        if (0 === $res['errcode']) {
            return $res['department'];
        } else {
            throw new Exception($res['errmsg']);
        }
    }

    /**
     * 获取部门用户
     * @param integer $department_id 部门id
     * @return array
     * @throws Exception
     */
    public function getDepartmentUser($department_id)
    {
        $url = $this->department_user_url.'?access_token='.$this->access_token.'&department_id='.intval($department_id);
        $result = Http::curlGet($url);
        $res = json_decode($result, true);
        if (0 === $res['errcode']) {
            return $res['userlist'];
        } else {
            throw new Exception($res['errmsg']);
        }
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
        $url = $this->company_message_send_url.'?access_token='.$this->access_token;
        $requestData = [
            'touser' => $toUser,
            'toparty' => $toParty,
            'agentid' => $this->agentid,
            'msgtype' => $text->msgtype,
            'text' => $text->text,
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        $res = json_decode($result, true);
        if (0 === $res['errcode']) {
            return true;
        } else {
            throw new Exception($res['errmsg']);
        }
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
        $url = $this->chat_create_url.'?access_token='.$this->access_token;
        $requestData = [
            'name' => strval($name),
            'owner' => strval($owner),
            'useridlist' => $useridlist
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        $res = json_decode($result, true);
        if (0 === $res['errcode']) {
            return $res['chatid'];
        } else {
            throw new Exception($res['errmsg']);
        }
    }

    /**
     * 向群里发文本消息
     * @param string $chatId 群会话的id
     * @param string $sender 发送者的userid
     * @param Text $text 消息对象
     * @return bool
     * @throws Exception
     */
    public function chat_send($chatId, $sender, Text $text)
    {
        $url = $this->chat_send_url.'?access_token='.$this->access_token;
        $requestData = [
            'chatid' => strval($chatId),
            'sender' => strval($sender),
            'msgtype' => $text->msgtype,
            'text' => $text->text
        ];
        $result = Http::curlPost($url, $requestData, ['Content-Type:application/json']);
        $res = json_decode($result, true);
        if (0 === $res['errcode']) {
            return true;
        } else {
            throw new Exception($res['errmsg']);
        }
    }
}
