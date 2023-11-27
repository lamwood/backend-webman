<?php
namespace Backend\webman\exception;
/**
 * Description of Error
 *
 * @author Administrator
 */
class Error {
    /**
     * Error Handler
     * @param int $errno 错误编号
     * @param string $errstr 详细错误信息
     * @param string $errfile 出错的文件
     * @param int $errline 出错行号
     * @throws ErrorException
     */
    public function appError(int $errno, string $errstr, string $errfile = '', int $errline = 0): void{
        if(!$this->isDeprecation($errno)){
            if(error_reporting() & $errno){
                throw new \RuntimeException($errstr, $errno);
            }
        }
    }
    //
    protected function isDeprecation($level){
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }
}
