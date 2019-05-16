<?php
/**
 * 消息路由 来源 融云
 */
define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/Lib/AutoLoader.php';
use Easemob\EasemobApi as EasemobApi;

function writeLog($params) {
    if (is_array($params)) {
        $params = json_encode($params);
    }
    $file = "./1.txt";
    $han = fopen($file, 'a+');
    fwrite($han, $params);
    fclose($han);
}

const CLIENTID = 'CLIENTID';
const SECRET = 'SECRET';

$arrParams = $_POST;
$channel = $_POST['channelType'];
$msgType = $_POST['objectName'];
$fromUser = $_POST['fromUserId'];
$toUser = $_POST['toUserId'];

//加入判断逻辑，是否需要转发信息

$content = $_POST['content'];    //消息内容
$easeChannel = 'users'; //消息通道
try {
    $easemobApi = EasemobApi::getInstance(CLIENTID, SECRET);
    switch ($channel) {
        case 'GROUP':
            $easeChannel = 'chatgroups';
            break;
        default:
            break;
    }
    switch ($msgType) {
        case 'RC:ImgMsg':
            $content = json_decode($content, true);
            $filePath = Base::getFilePath($content['imageUri']);
            $arrRes = $easemobApi->uploadFile($filePath);
            $secret = $arrRes['entities'][0]['share-secret'];
            $objContent = array(
                'type' => 'img',
                'url' => $arrRes['url'],
                "secret" => $secret // 成功上传文件后返回的secret
            );
            break;
        case 'RC:VcMsg':
            $content = json_decode($content, true);
            $filePath = Base::vc2File($content['content']);
            $arrRes = $easemobApi->uploadFile($filePath);
            $secret = $arrRes['entities'][0]['share-secret'];
            $objContent = array(
                'type' => 'audio',
                'url' => $arrRes['url'],
                'secret' => $secret,
                'length'=> $content['duration']
            );
            break;
        case 'RC:ImgTextMsg':
            $content = json_decode($content, true);
            $filePath = Base::getFilePath($content['imageUri']);
            $arrRes = $easemobApi->uploadFile($filePath);
            $secret = $arrRes['entities'][0]['share-secret'];
            $objContent = array();
            $objContent[0] = array(
                'type' => 'img',
                'url' => $arrRes['url'],
                "secret" => $secret // 成功上传文件后返回的secret
            );
            $objContent[1] = array(
                'type' => 'txt',
                'msg' => '标题：' . $content['title'] . ' 内容' . $content['content']
            );
            break;
        default:
            $content = json_decode($content, true);
            writeLog($content);
            $objContent = array(
                'type' => 'txt',
                'msg' => $content['content']
            );
            break;
    }
    if (isset($objContent[0])) {
        foreach ($objContent as $key => $value) {
            $arrRes[$key] = $easemobApi->messagePublish($fromUser, array($toUser), $value, $content['extra'], $easeChannel);
        }
    } else {
        $arrRes = $easemobApi->messagePublish($fromUser, array($toUser), $objContent, $content['extra'], $easeChannel);
    }
    writeLog($arrRes);
} catch(Exception $e) {
    writeLog($e->getMessage());
}



