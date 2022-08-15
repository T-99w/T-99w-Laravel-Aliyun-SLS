<?php

namespace App\Tools\AliyunSLS;

use Aliyun_Log_Client;
use Aliyun_Log_Exception;
use Aliyun_Log_Models_LogItem;
use Aliyun_Log_Models_PutLogsRequest;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class AliyunHandler extends AbstractProcessingHandler
{

    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $project;
    protected $logStore;
    /*
     * 全局配置参数key
     * */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->accessKeyId = config('aliyunlog.access_key_id');
        $this->accessKeySecret = config('aliyunlog.access_key_secret');
        $this->endpoint = config('aliyunlog.sls_endpoint');
        $this->project = config('aliyunlog.sls_project');
        $this->logStore = config('aliyunlog.sls_store');
        parent::__construct($level, $bubble);
    }

    /**
     * 将错误和日志记录到阿里云
     * @param array $record
     * @throws Aliyun_Log_Exception
     */
    protected function write(array $record):void
    {
        //这里如果是error错误，阿里云日志的topic统一是error，其他都是自定义（即调用laravel自带的Log工具类时给的标识）
        if ($record['level'] == Logger::ERROR) {
            $topic = strtolower($record['level_name']);
        } else {
            $topic = $record['message'];
        }

        //保存日志信息
        $data = [
            '内容' => json_encode($record['context'], JSON_UNESCAPED_UNICODE),
            '等级' => $record['level_name'],
            '请求方式' => request()->getMethod(),
            '路由地址' => request()->getRequestUri(),
            '请求参数' => $this->getRequestData(),
            '时间' => date('Y-m-d H:i:s', time()),
            '标题' => $topic,
            'ip' => request()->getClientIp()
        ];

        //创建日志服务Client。
        $client = new Aliyun_Log_Client($this->endpoint,$this->accessKeyId, $this->accessKeySecret);

        //创建日志对象
        $logItem = new Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($data);
        $logitems = array($logItem);

        //创建Logstore并且提交
        $request = new Aliyun_Log_Models_PutLogsRequest($this->project, $this->logStore,$topic, null, $logitems);
        $client->putLogs($request);
    }

    /*
     * 请求参数记录
     * */
    protected function getRequestData()
    {
        $requestJsonData = empty(request()->getContent()) ? [] : json_decode(request()->getContent(), true);
        $postData = empty(request()->post()) ? [] : request()->post();
        $data = [
            '__get__' => empty(request()->query()) ? [] : request()->query(),
            '__post__' => array_merge($postData , $requestJsonData),
            '__put__' => $requestJsonData,
            '__delete' => $requestJsonData
        ];
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
