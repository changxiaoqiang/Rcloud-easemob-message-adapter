<?php
/**
* 基础类库
*/
class Base {

    /**
     * 重写实现 http_build_query 提交实现(同名key)key=val1&key=val2
     * @param array $formData 数据数组
     * @param string $numericPrefix 数字索引时附加的Key前缀
     * @param string $argSeparator 参数分隔符(默认为&)
     * @param string $prefixKey Key 数组参数，实现同名方式调用接口
     * @return string
     */
    private static function build_query($formData, $numericPrefix = '', $argSeparator = '&', $prefixKey = '') {
        $str = '';
        foreach ($formData as $key => $val) {
            if (!is_array($val)) {
                $str .= $argSeparator;
                if ($prefixKey === '') {
                    if (is_int($key)) {
                        $str .= $numericPrefix;
                    }
                    $str .= urlencode($key) . '=' . urlencode($val);
                } else {
                    $str .= urlencode($prefixKey) . '=' . urlencode($val);
                }
            } else {
                if ($prefixKey == '') {
                    $prefixKey .= $key;
                }
                if (is_array($val[0])) {
                    $arr = array();
                    $arr[$key] = $val[0];
                    $str .= $argSeparator . http_build_query($arr);
                } else {
                    $str .= $argSeparator . self::build_query($val, $numericPrefix, $argSeparator, $prefixKey);
                }
                $prefixKey = '';
            }
        }
        return substr($str, strlen($argSeparator));
    }

    /**
     * 发起 server 请求
     * @param $action
     * @param $params
     * @param $httpHeader
     * @return mixed
     */
    public static function curl($action, $params, $httpHeader = array()) {
        $ch = curl_init();
        $strReg = '/application\/json/i';
        $data = $params;
        if (count($httpHeader) > 0 && preg_match($strReg, $httpHeader[0])) {
            $data = json_encode($data);
        } elseif(!preg_match('/application\/octet-stream/i', $httpHeader[0])) {
            $data = self::build_query($data);
        } else {
            unset($httpHeader[0]);
        }
        print_r($data);
        print_r($httpHeader);
        curl_setopt($ch, CURLOPT_URL, $action);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($ch);
        $rInfo = curl_getinfo($ch);
        print_r($ret);
        print_r($rInfo);
        if ($rInfo['http_code'] == '200') {
            $arrResult = json_decode($ret, true);
            $arrResult = array('code' => 200, 'msg' => $arrResult);
        } else {
            $arrResult = array('code' => $rInfo['http_code'], 'msg' => $ret);
        }
        curl_close($ch);
        return $arrResult;
    }

    /**
     * 从远程获取文件对象
     */
    public function getFilePath($url) {
        $filePath = '/tmp/' . time() . mt_rand();
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $content=curl_exec($ch);
        curl_close($ch);
        $fhan= @fopen($filePath, 'a');
        fwrite($fhan, $content);
        fclose($fhan);
        return $filePath;
    }

    /**
     * 将语音转化为 文件
     */
    public function vc2File($str) {
        $filePath = '/tmp/' . time() . mt_rand();
        $strVc = base64_decode($str);
        $fhan= @fopen($filePath, 'a');
        fwrite($fhan, $strVc);
        fclose($fhan);
        return $filePath;
    }

    /**
     * 获取缩略图 base64 编码
     */
    public function getThumb($url) {
        $str = base64_encode(file_get_contents($url));
        $thumbContent = str_replace("/r/n", '', $str);
        return $thumbContent;
    }

    public function img2Thumb($url) {
        $filePath = self::getFilePath($url);
        $dstPath = '/tmp/' . time() . mt_rand();
        list($width, $height, $type, $attr)=getimagesize($filePath);
        $tmpWidth = $tmpHeight = 240;
        if ($width >= $tmpWidth) {
            $rate = $tmpWidth / $width;
            $height = $height * $rate;
            $width = 240;
        }
        if ($height >= $tmpHeight) {
            $rate = $tmpHeight / $height;
            $width = $width * $rate;
            $height = 240;
        }
        if(!is_file($filePath))
        {
            return false;
        }
        //$ot = fileext($dstPath);
        $ot = 'png';

        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($filePath);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
        {
            $proportion = 1;
        }
        if($width> $src_w)
        {
            $dst_w = $width = $src_w;
        }
        if($height> $src_h)
        {
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion)
        {
            return false;
        }
        if(!$proportion)
        {
            if($cut == 0)
            {
                if($dst_w && $dst_h)
                {
                    if($dst_w/$src_w> $dst_h/$src_h)
                    {
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }
                    else
                    {
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h)
                {
                    if($dst_w && !$dst_h)  //有宽无高
                    {
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h)  //有高无宽
                    {
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else
            {
                if(!$dst_h)  //裁剪时无高
                {
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w)  //裁剪时无宽
                {
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($filePath);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dstPath);

        return Base::getThumb($dstPath);

    }
}
