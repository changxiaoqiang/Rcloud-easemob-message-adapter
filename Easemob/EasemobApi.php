<?php

/**
 * 实现 环信 API 接口
 */

namespace Easemob;
use Base;
use Exception;

class EasemobApi {
    private static $orgName = 'lucky198600', $appName = 'wakaka2', $token,
        $url = 'https://a1.easemob.com/', $instance, $header;

    /**
     * 参数初始化
     * @param $appKey
     * @param $appSecret
     * @param string $format
     */
    private function __construct($client_id, $client_secret) {
        self::$url .= self::$orgName . '/' . self::$appName . '/';
        $action = 'token';
        $arrToken = Base::curl(self::$url . $action,
            array(
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ),
            array('Content-Type:application/json')
        );
        if ($arrToken['code'] != 200) {
            throw new Exception($arrToken['msg'], $arrToken['code']);
        } else {
            print_r($arrToken);
            self::$token = $arrToken['msg']['access_token'];
            self::$header = array(
                'Content-Type:application/json',
                'Authorization:Bearer ' . self::$token
            );
        }
    }

    /**
     * 单例，保证只有一个实例
     */
    public static function getInstance($client_id, $client_secret) {
        if (!is_object(self::$instance)) {
            self::$instance = new self($client_id, $client_secret);
        }
        return self::$instance;
    }

    /**
     * 上传图片
     */
    public function uploadFile($fileUrl) {
        $url = self::$url . 'chatfiles';
        $file = $fileUrl;
        $postData = array(
            'file' => "@$file"
        );
        $arrHeader = self::$header;
        $arrHeader[0] = 'Content-Type:application/octet-stream';
        $arrHeader[] = 'restrict-access:true';
        $arrRes = Base::curl($url, $postData, $arrHeader);

        if ($arrRes['code'] != 200) {
            throw new Exception($arrRes['msg'], $arrRes['code']);
        } else {
            $arrRes['msg']['url'] = $url . '/' . $arrRes['msg']['entities'][0]['uuid'];
            return $arrRes['msg'];
        }
    }

    /**
     * 发送会话消息
     * @param $fromUserId   发送人用户 Id。（必传）
     * @param $toUserId     接收用户 Id，提供多个本参数可以实现向多人发送消息。（必传）
     * @param $objContent   消息
     * @param string $extContent   扩展属性
     * @param string $target_type   users 给用户发消息, chatgroups 给群发消息
     * @return json|xml
     */
    public function messagePublish($fromUserId, $toUserId = array(), $objContent, $extContent = array(), $target_type) {
        $action = 'messages';

        $params = array(
            'target_type' => $target_type,
            'target' => $toUserId,
            'msg' => $objContent,
            'from' => $fromUserId,
            'ext' => $extContent
        );
        if (!$fromUserId) {
            unset($params['from']);
        }
        if (!$extContent) {
            unset($params['ext']);
        }
        $arrRes = Base::curl(
            self::$url . $action,
            $params,
            self::$header
        );

        if ($arrRes['code'] != 200) {
            throw new Exception($arrRes['msg'], $arrRes['code']);
        } else {
            return $arrRes['msg'];
        }
    }
}
