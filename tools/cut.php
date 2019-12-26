<?php
define('MAX_LINE', 10000);
define('IN_DIR', 'D:\\data\\10-million-combos');
define('OUT_DIR', 'D:\data\\10-million-combos\\outs');

$in = fopen(IN_DIR."/10-million-combos.txt", "r");

$line = 0;
$fileIndex = 0;
is_dir(OUT_DIR) || mkdir(OUT_DIR, "0644");
$out = fopen(sprintf(OUT_DIR."/%d.log", $fileIndex), "w");

while (($buffer = fgets($in, 4096)) !== false) {
    fputs($out, $buffer);
    ++$line;
    if ($line > MAX_LINE-1) {
        $line = 0;
        fclose($out);
        ++$fileIndex;
        $out = fopen(sprintf(OUT_DIR."/%d.log", $fileIndex), "w");
    }
}
fclose($out);