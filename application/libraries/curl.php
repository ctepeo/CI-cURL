<?php

/*
    cURL library for CodeIgniter
    created by George Sazanovich (ctepeo)
    
    Please be free to ask and contribute via github
    https://github.com/ctepeo/CI-cURL/
    
    This code distributed with The MIT License (MIT), so be free to use it anywhere

*/


class Curl {
    /*
     * cURL settings
     */

    //  debug cURL actions
    var $debug = TRUE;
    //  recieve response?
    var $getContent = TRUE;
    //  recieve response's headers?
    var $getHeaders = FALSE;
    //  follow server's redirect?
    var $followLocation = TRUE;
    //  max redirects
    var $maxRedirects = 10;
    //  ignore SSL certificates?
    var $ignoreSSL = TRUE;
    //  timeout on connect 
    var $connectTimeout = 60;
    //  timeout on response
    var $timeout = 60;
    //  use random User-Agent
    var $randomUserAgent = TRUE;
    //  multicurl 
    var $multicurl = TRUE;
    //  multicurl max connections
    var $multicurlmax = 7;
    //  multicurl handler
    var $multihandler = FALSE;
    //  multicurl container
    var $multicontainer = array();
    //  var to store logs
    var $log;
    //  cURL handler
    var $handler;
    var $userAgents = array(
        //  Google Chrome
        'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36',
        'Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13',
        //  Mozilla Firefox
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/20100101 Firefox/25.0',
        'Mozilla/5.0 (Windows NT 6.0; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0',
        //  Opera
        'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
        'Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14',
        'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14',
        'Opera/12.0(Windows NT 5.1;U;en)Presto/22.9.168 Version/12.00',
        //  Safari
        'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
        'Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'
    );
    //  Default headers
    var $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Pragma' => 'no-cache'
    );

    function __construct() {
        $this->_init();
    }

    function cleanup() {
        if ($this->multicurl === TRUE && count($this->multicontainer) > 0)
            return $this->_execMulti();
        return false;
    }

    function _init() {
        $this->handler = curl_init();
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, !$this->ignoreSSL);
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, !$this->ignoreSSL);
        curl_setopt($this->handler, CURLOPT_VERBOSE, $this->debug);
        if ($this->debug) {
            $this->log = fopen('php://temp', 'rw+');
            curl_setopt($this->handler, CURLOPT_STDERR, $this->log);
            curl_setopt($this->handler, CURLINFO_HEADER_OUT, $this->debug);
        }
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, $this->getContent);
        curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($this->handler, CURLOPT_HEADER, $this->getHeaders);
        curl_setopt($this->handler, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($this->handler, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->handler, CURLOPT_MAXREDIRS, $this->maxRedirects);
        if ($this->randomUserAgent === TRUE)
            $this->_setHeader('User-Agent', $this->userAgents[rand(0, count($this->headers))]);
        if ($this->multicurl && $this->multihandler === FALSE)
            $this->multihandler = curl_multi_init();
    }

    function _setHeader($key, $value) {
        $this->headers[$key] = $value;
    }

    function _acceptHeaders() {
        curl_setopt($this->handler, CURLOPT_HTTPHEADER, array_map(function($value, $key) {
                    return $key . ":" . $value;
                }, $this->headers, array_keys($this->headers)));
    }

    function _setURL($url) {
        if (!isset($this->headers['Host']))
            $this->_setHeader('Host', parse_url($url)['host']);
        curl_setopt($this->handler, CURLOPT_URL, $url);
    }

    function _setPostdata($post = array()) {
        $post = http_build_query($post);
        curl_setopt($this->handler, CURLOPT_POST, TRUE);
        curl_setopt($this->handler, CURLOPT_POSTFIELDS, $post);
        $this->_setHeader('Content-length', strlen($post));
    }

    function _appendMulti($key = false) {
        $this->_acceptHeaders();
        if ($key) {
            $this->multicontainer[$key] = $this->handler;
        } else {
            $this->multicontainer[] = $this->handler;
        }
        curl_multi_add_handle($this->multihandler, $this->handler);
        unset($this->handler);
        $this->_init();
    }

    function _execMulti() {
        $active = null;
        do {
            $status = curl_multi_exec($this->multihandler, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);
        foreach ($this->multicontainer as $i => $url) {
            $res[$i] = curl_multi_getcontent($this->multicontainer[$i]);
            if ($this->debug) {
                echo '<b>Debug info</b><br>';
                var_dump(curl_getinfo($this->multicontainer[$i]));
                echo $this->multicontainer[$i]['request_header'];
            }
            curl_multi_remove_handle($this->multihandler, $this->multicontainer[$i]);
            curl_close($this->multicontainer[$i]);
        }
        return $res;
    }

    function get($url = array(), $key = false) {
        if (is_array($url) && count($url) > 0) {
            foreach ($url as $key => $request) {
                $result[$key] = $this->get($request, $key);
            }
            $result[] = $this->cleanup();
            $result = array_values(array_filter($result, function($el) {
                        return(!empty($el));
                    }));
            $objTmp = (object) array('aFlat' => array());
            array_walk_recursive($result, create_function('&$v, $k, &$t', '$t->aFlat[$k] = $v;'), $objTmp);
            return $objTmp->aFlat;
        }
        $this->_setURL($url);
        if ($this->multicurl) {
            $this->_appendMulti($key);
            if (count($this->multicontainer) >= $this->multicurlmax) {
                $response = $this->_execMulti();
                $this->multicontainer = array();
            }
        } else {
            $this->_acceptHeaders();
            $response[$key] = curl_exec($this->handler);
            curl_close($this->handler);
            $this->_init();
        }
        if (!$this->multicurl && $this->debug) {
            !rewind($this->log);
            echo "CURL Debug:\n<pre>", htmlspecialchars(stream_get_contents($this->log)), "</pre>\n";
        }
        return isset($response) ? $response : false;
    }

    function post($url = array(), $post = array(), $key = false) {
        if (is_array($url) && count($url) > 0) {
            foreach ($url as $key => $request) {
                $result[$key] = $this->post($request, $post, $key);
            }
            $result[] = $this->cleanup();
            $result = array_values(array_filter($result, function($el) {
                        return(!empty($el));
                    }));
            $objTmp = (object) array('aFlat' => array());
            array_walk_recursive($result, create_function('&$v, $k, &$t', '$t->aFlat[$k] = $v;'), $objTmp);
            return $objTmp->aFlat;
        }
        $this->_setURL($url);
        $this->_setPostdata($post);
        if ($this->multicurl) {
            $this->_appendMulti($key);
            if (count($this->multicontainer) >= $this->multicurlmax) {
                $response = $this->_execMulti();
                $this->multicontainer = array();
            }
        } else {
            $this->_acceptHeaders();
            $response[$key] = curl_exec($this->handler);
            curl_close($this->handler);
            $this->_init();
        }
        if (!$this->multicurl && $this->debug) {
            !rewind($this->log);
            echo "CURL Debug:\n<pre>", htmlspecialchars(stream_get_contents($this->log)), "</pre>\n";
        }
        return isset($response) ? $response : false;
    }

}

?>
