<?php

class Logger
{
    /** @var resource */
    protected static $handler = null;
    const MAX_LOG_SIZE = 1048576;
    private static function init() {
        $logDir = dirname(__FILE__).'/out';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logPath = $logDir.'/scan.log';
        if (!is_resource(self::$handler)) {
            self::$handler = fopen($logPath, 'a');
        }
        $stat = fstat(self::$handler);
        if ($stat['size'] > self::MAX_LOG_SIZE) {
            copy($logPath, $logDir.'/scan.archive.log');
            ftruncate(self::$handler, 0);
            rewind(self::$handler);
        }
    }
    public static function write($msg) {
        self::init();
        // datetime
        $msg = sprintf("[%s] %s\n", date("Y-m-d H:i:s", time()), $msg);
        fwrite(self::$handler, $msg, strlen($msg));
    }
    public static function close() {
        if (is_resource(self::$handler)) {
            fclose(self::$handler);
            self::$handler = null;
        }
    }
}