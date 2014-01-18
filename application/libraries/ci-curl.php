<?php

class Curl {

    var $debug = true;
    var $ignoreSSL = true;
    var $debug_log = "";
    var $ch = "";
    var $userAgents = array(
        //  chrome
        'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36',
        'Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13',
        //  firefox
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/20100101 Firefox/25.0',
        'Mozilla/5.0 (Windows NT 6.0; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0',
        //  opera
        'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
        'Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14',
        'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14',
        'Opera/12.0(Windows NT 5.1;U;en)Presto/22.9.168 Version/12.00',
        //  safari
        'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
        'Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'
    );
    var $headers = array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Pragma: no-cache'
    );

    function __construct() {
        parent::__construct();
    }

    function setDebug($enable = true) {
        $this->debug = (bool) $enable;
    }

    function debug() {
        if ($this->ignoreSSL) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        if ($this->debug) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
            $this->debug_log = fopen('php://temp', 'rw+');
            curl_setopt($this->ch, CURLOPT_STDERR, $this->debug_log);
        }
        $this->addHeader('User-Agent', $this->userAgents[rand(0, 15)]);
    }

    function addHeader($key, $value) {
        if (!in_array($key . ": " . $value, $this->headers))
            $this->headers[] = $key . ": " . $value;
    }

    function addHeaders($headers = array()) {
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->addHeader($key, $value);
            }
        }
    }

    function get($url, $headers = array(), $getHeaders = false) {
        $this->ch = curl_init($url);
        $this->debug();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
        if ($getHeaders) {
            curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
        }
        if (!empty($headers))
            $this->addHeaders($headers);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($this->ch);
        curl_close($this->ch);
        if ($this->debug) {
            !rewind($this->debug_log);
            echo "CURL Debug:\n<pre>", htmlspecialchars(stream_get_contents($this->debug_log)), "</pre>\n";
        }
        return $response;
    }

    function post($url, $post = array(), $headers = array(), $getHeaders = false) {
        $post = http_build_query($post);
        $ch = curl_init($url);
        $this->debug();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        if ($getHeaders) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $this->addHeader('Content-length', strlen($post));
        if (!empty($headers))
            $this->addHeaders($headers);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($this->debug) {
            !rewind($this->debug_log);
            $verboseLog = stream_get_contents($this->debug_log);
            echo "CURL Debug:\n<pre>", htmlspecialchars($this->debug_log), "</pre>\n";
        }
        return $response;
    }

}

?>