<?php
/**
 * Description of autoloader
 *
 * @author changshuaiqiang
 */
class AutoLoader {

    /**
     * IM 通讯库，使用命名空间方式
     */
    public static function imAutoLoader($class) {
        $arr = explode("\\", $class);
        $intLen = count($arr);

        $class = $arr[--$intLen];
        $path = str_replace('\\', '/', dirname(__FILE__))."/../$arr[0]/{$class}.php";

        if (is_readable($path)) {
            require $path;
        }
    }

    /**
     * 基本函数类库
     */
    public static function libAutoLoader($class){
        $arr = explode("\\", $class);
        $intLen = count($arr);

        $class = $arr[--$intLen];
        $path = str_replace('\\', '/', dirname(__FILE__))."/../Lib/{$class}.php";

        if (is_readable($path))
            require $path;
    }
}

spl_autoload_register("AutoLoader::imAutoLoader");

spl_autoload_register("AutoLoader::libAutoLoader");


