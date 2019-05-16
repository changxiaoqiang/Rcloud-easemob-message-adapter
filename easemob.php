<?php
/**
 * 消息路由入口，消息来源为环信
 */
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/Lib/AutoLoader.php';
use RCloud\RCloudApi as RCloudApi;

function writeLog($params) {
    if (is_array($params)) {
        $params = json_encode($params);
    }
    $file = "./2.txt";
    $han = fopen($file, 'a+');
    fwrite($han, $params);
    fclose($han);
}

const APPKEY = 'APPKEY';
const APPSECRET = 'APPSECRET';

$strParams = file_get_contents("php://input");
$arrParams = json_decode($strParams, true);

$channel = $arrParams['chat_type'];
$fromUser = $arrParams['from'];
$toUser = $arrParams['to'];
$msg = $arrParams['payload']['bodies'][0];
$msgType = $msg['type'];
$extra = $arrParams['payload']['ext'];

//加入判断逻辑，是否需要转发信息

try {
    $RCloudApi = RCloudApi::getInstance(APPKEY, APPSECRET);
    $rcloudChannel = 'messagePublish';
    switch ($channel) {
        case 'groupchat':
            $rcloudChannel = 'messageGroupPublish';
        default:
            break;
    }

    switch ($msgType) {
        case 'txt':
            $content = $msg['msg'];
            $objContent = array(
                'content' => $content,
                'extra' => $extra
            );
            $objName = 'RC:TxtMsg';
            break;
        case 'img':
            $imgUrl = $msg['url'];
            $thumbContent = Base::img2Thumb($msg['url']);
            writeLog($thumbContent);
            $objContent = array(
                'content' => $thumbContent,
                'imageUri' => $imgUrl,
                'extra' => $extra
            );
            $objName = 'RC:ImgMsg';
            break;
        case 'audio':
            $audioUrl = $msg['url'];
            $thumbContent = Base::getThumb($audioUrl);
            $duration = $msg['length']; //时长
            $objContent = array(
                'content' => $thumbContent,
                'duration' => $duration,
                'extra' => $extra
            );
            $objName = 'RC:VcMsg';
            break;
        case 'loc':
            $objContent = array(
                'content' => '',
                'latitude' => $msg['lat'],
                'longitude' => $msg['lng'],
                'extra' => $extra
            );
            $objName = 'RC:LBSMsg';
            break;
    }
    $objContent = json_encode($objContent);
    $arrRes = $RCloudApi->$rcloudChannel($fromUser, $toUser, $objName, $objContent);
    writeLog($arrRes);
} catch (Exception $e) {
    writeLog($e->getMessage());
}

