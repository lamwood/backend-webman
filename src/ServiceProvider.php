<?php
namespace Backend\webman;

use Backend\webman\exception\Error;
use Symfony\Component\Filesystem\Filesystem;
use support\Container;
use Webman\Bootstrap;
use Workerman\Worker;

/**
 * Description of ServiceProvider
 *
 * @author Administrator
 */
class ServiceProvider implements Bootstrap{
    /**
     * 
     * @param Worker|null $worker
     * @return void
     */
    public static function start($worker){
        set_error_handler([Container::make(Error::class), 'appError']);
        self::updateVersion();
        self::init();
        \ExAdmin\ui\support\Container::getInstance()->plugin->register();
    }
    //
    public static function init(){
        
    }
    //
    protected static function updateVersion(){
        $file = public_path('exadmin').DIRECTORY_SEPARATOR.'version';
        $update = false;
        if(is_file($file)){
            $update = true;
        }
        //
    }
}
