<?php

class File {

    const BUFSIZE = 1024;
    /**
     * @param $path
     * @param $handler
     * @param string $comment
     * @throws Exception
     */
    public static function forEachRow($path, $handler, $comment = '#') {
        $handle = fopen($path, "r");
        if (!$handle) {
            return;
        }
        while (($buffer = fgets($handle, self::BUFSIZE)) !== false) {
            $buffer = trim($buffer);
            preg_match('/^'.$comment.'.*$/', $buffer, $matches);
            if (!empty($matches)) {
                continue;
            }
            call_user_func($handler, $buffer);
        }
        if (!feof($handle)) {
            fclose($handle);
            throw new Exception("Error: unexpected fgets() fail", 77);
        }
        fclose($handle);
    }

    /**
     * 把文件定位到指定行
     * @param $fp resource
     * @param $lineNum int
     * @return mixed
     */
    public static function seekLine($fp, $lineNum) {
        $offset = 0;
        $n = 1;
        while (($buffer = fgets($fp, self::BUFSIZE)) !== false) {
            if ($n < $lineNum) {
                $n += 1;
                $offset += strlen($buffer);
            } else {
                break;
            }
        }
        fseek($fp, $offset);
        return $fp;
    }
   
}