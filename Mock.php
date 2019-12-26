<?php
 
class Mock {

    public static $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    
    public static $letters = "abcdefghijklmnopqrstuvwxyz";
 
    public static $numeric = "0123456789";
 
    public static $email_suffix =
                                ["@gmail.com", "@yahoo.com", "@msn.com", "@hotmail.com", "@aol.com", "@ask.com",
                                 "@live.com", "@qq.com", "@0355.net", "@163.com", "@163.net",
                                 "@263.net", "@3721.net", "@yeah.net", "@126.com", "@sina.com", "@sohu.com", "@yahoo.com.cn"];
    public static $mobile_prefix = ["134", "135", "136", "137", "138", "139", "150", "151", "152", "157", "158", "159", "130",
                                    "131", "132", "155", "156", "133", "153"];
    public static $grade = ['高三','高二','高一','初三','初二','初一','小六','六年级','七年级','八年级','九年级','高中','初中','小学'];
 
    public static $gradeValue = ["03-2016", "03-2017", "03-2018", "02-2016", "02-2017", "02-2018", "02-2015", "02-2016", "02-2017", "02-2018", "01-2013"];
 
    public static function getNumber(/* int */$width) /* int */ {
        $min = 1;
        if ($width <= 1) {
            return rand(0, 9);
        }
        $width -= 1;
        for ($i = 0; $i <$width; $i++) {
            $min *= 10;
        }
        $max = $min * 10 - 1;
        return rand($min, $max);
    }
 
    public static function getMobile() {
        return self::random(1, self::$mobile_prefix) . self::random(8, self::$numeric);
    }
 
    public static function getGrade() {
        return self::random(1, self::$grade);
    }
 
    public static function getGradeValue() {
        return self::pick(self::$gradeValue);
    }
 
    public static function getElement($list) {
       if (is_string($list)) {
            $n = strlen($list);
        } else if (is_array($list)) {
            $n = count($list);
        } else {
            throw new InvalidArgumentException("list must string or array");
        }
        return $list[rand(0, $n-1)];
    }
 
    public static function getName() {
        return self::random(8, self::$letters);
    }
 
    private static function random(/*int */$length, /* ArrayAccess */ $list) {
        if ($length <= 1) {
            $length = 1;
        }
        $s = "";
        if (is_string($list)) {
            $n = strlen($list);
        } else if (is_array($list)) {
            $n = count($list);
        } else {
            throw new InvalidArgumentException("list must string or array");
        }
 
        while ($length--) {
            $s .= $list[ rand(0, $n-1) ];  // inclusive $n-1
        }
        return $s;
    }
 
    public static function pick($list) {
        $n = count($list);
        if ($n < 1) {
            throw new RunTimeException("Empty list");
        }
        return $list[ rand(0, $n-1) ];
    }

    public static function getInviteCode() {
        return self::random(8, self::$upper);
    }
 
    public static function main() {
        echo self::getNumber(5).PHP_EOL;
        echo self::getMobile().PHP_EOL;
        echo self::getGrade().PHP_EOL;
        echo self::getInviteCode().PHP_EOL;
    }
 
}
 
// Mock::main();
