<?php
/**
 * Created by PhpStorm.
 * User: Mch
 * Date: 2019-04-05
 * Time: 18:37
 */

class CodeGenerator {
    /** @var string */
    private $begin;

    /** @var string */
    private $end;

    private $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    // private static $chars = "0123456789";

    /**
     * CodeGenerator constructor.  [$begin, $end)
     * @param $begin string
     * @param $end string
     * @param $chars string 可能出现的字符列表
     */
    public function __construct($begin, $end = '', $chars = '') {
        $this->begin = $begin;
        if (empty($end)) {
            $n = strlen($this->chars);
            $m = strlen($begin);
            $this->end = "";
            while ($m-- > 0) {
                $this->end .= $this->chars[$n-1];
            }
        } else {
            $this->end = $end;
        }
        if ($chars) {
            $this->chars = $chars;
        }
    }

    private static function add($chars, $s) {
        $n = strlen($chars);
        $m = strlen($s);
        if ($m < 1) {
            return "";
        }
        //  if (!isset($s[0])) {return $chars[0];}
        if ( $m < 2 ) {
            $pos = strpos($chars, $s[0]) + 1;
            if ($pos < $n) {
                return $chars[$pos];
            }
            return self::add($chars, $s[1]).$chars[0];
        }
        $pos = strpos($chars, $s[$m-1])+1;
        if ($pos < $n) {
            $s[$m-1] = $chars[$pos];
            return $s;
        }
        return self::add($chars, substr($s, 0, $m-1)).$chars[0];
    }

    public function process(callable $fn) {
        $chars = $this->chars;
        for ($code = $this->begin; $code != $this->end; $code = self::add($chars, $code)) {
            call_user_func($fn, $code);
        }
    }
}
