<?php

class RequestClient
{
    /** @var string */
    protected $url;

    /** @var array assoc */
    protected $requestHeaders = [];

    /** @var string */
    protected $requestBody = "";

    /** @var int */
    protected $statusCode = 0;

    /** @var string */
    protected $statusMsg = "";

    /** @var array assoc */
    protected $responseHeaders = [];

    /** @var string */
    protected $responseBody = "";

    /** @var string */
    protected $protocol;

    /** @var int curl errno */
    protected $chErrno = 0;

    /** @var string curl error string */
    protected $chError = "";

    public function __construct() {
        $this->ch = curl_init();
    }

    /**
     * $.param
     * @param $map array assoc
     * @return string
     */
    public static function params($map) {
        $a = [];
        array_walk($map, function($value, $name) use (&$a) {
            array_push($a, sprintf("%s=%s", $name, urlencode($value)));
        });
        return implode('&', $a);
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * 设置http请求头 可以调用多次
     */
    public function setRequestHeader($name, $value) {
        $this->requestHeaders[ $name ] = $value;
    }

    /**
     * 设置http请求body 只能调用一次
     * @param $body
     */
    public function setRequestBody($body) {
        $this->requestBody = $body;
    }

    /**
     * GET请求RequestBody为空
     */
    public function get() {
        $this->requestBody = "";
        $this->exec(0);
    }

    /**
     * @param $body
     */
    public function post($body = '') {
// 参数非空的情况下 覆盖原来的requestBody
        if (!empty($body)) {
            $this->requestBody = $body;
        }
        $this->exec(1);
    }

    private function exec($isPost) {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        // http request header
        $headers = [];
        foreach ($this->requestHeaders as $name => $value) {
            array_push($headers, $name.': '.$value);
        }
        // http request body
        if (empty($this->requestBody)) {
            // GET or: Empty request body POST
            curl_setopt($this->ch, CURLOPT_POST, $isPost);
        } else {
            // POST with request body
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->requestBody);
            curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
            // Content-Length: xx
            if (!isset($this->requestHeaders['Content-Length'])) {
                array_push($headers,
                    sprintf("Content-Length: %d", strlen($this->requestBody)));
            }
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        // 不验证https
        $matches = [];
        preg_match('/^https:\/\/.*$/', $this->url, $matches);
        if (! empty($matches) ) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);

        $data = curl_exec($this->ch);
        // curl on error
        $errno = curl_errno($this->ch);
        if ($errno) {
            $this->chErrno = $errno;
            $this->chError = curl_error($this->ch);
            return;
        }
        $lines = $this->handleHeaders($data);

        // http/2 100 continue\r\n\r\n
        // http/1.1 200 OK\r\n
        // ... 第1次处理的body部分作为第2次的head + body
        if ($this->statusCode === 100) {
            $data = self::extractHttpBody($data);
            $lines = $this->handleHeaders($data);
        }
        // set http response header assoc
        for ($i = 1; $i < count($lines); $i++) {
            $b = explode(": ", $lines[$i]);
            $this->responseHeaders[ $b[0] ] = $b[1];
        }

        // set http response body
        $this->responseBody = self::extractHttpBody($data);
    }

    /**
     * @param $data string response header + body
     * @return array headers
     */
    private function handleHeaders($data) {
        // curl success
        $respHeader = self::extractHttpHeader($data);
        // extract http response header and split lines
        $lines = explode("\r\n", $respHeader);
        // get status code "HTTP/1.1 200 OK"
        $a = explode(' ', $lines[0]);
        $this->protocol = $a[0];
        $this->statusCode = intval($a[1]);
        $this->statusMsg = isset($a[2]) ? $a[2]: ''; // http/2
        return $lines;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }


    /**
     * 取得curl响应的http body内容
     * @return string
     */
    public function getResponseBody() {
        return $this->responseBody;
    }

    public function getResponseHeaderByName($name) {
        if (!isset($this->responseHeaders[$name])) {
            return "";
        }
        return $this->responseHeaders[$name];
    }

    public function getResponseHeadersAsString() {
        $a = [
            implode(' ', [$this->protocol, $this->statusCode, $this->statusMsg])
        ];
        foreach ($this->responseHeaders as $name => $value) {
            array_push($a, $name.': '.$value);
        }
        return implode("\r\n", $a)."\r\n";
    }

    private static function findIndexFollowDelim($s, $delim, $startAt=0) {
        $n = strlen($delim);
        $j = 0;
        for ($i = $startAt; isset($s[$i]) && $j < $n; $i++) {
            if ($delim[$j] === $s[$i]) {
                $j += 1;
            } else {
                $j = 0;
            }
        }
        if (!isset($s[$i]) && $j < $n) {
            return -1;
        }
        return $i;
    }

    protected static function extractHttpBody($s, $delim = "\r\n\r\n") {
        return substr($s, self::findIndexFollowDelim($s, $delim));
    }

    protected static function extractHttpHeader($s, $delim = "\r\n\r\n") {
        $i = self::findIndexFollowDelim($s, $delim);
        if ($i < 0) {
            return $s;
        }
        $len = strlen($delim);
        return substr($s, 0, $i-$len);
    }

    public function __destruct() {
        curl_close($this->ch);
    }
}