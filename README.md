<h2>CI-cURL</h2>
=======

CodeIgniter library for using cURL easily.
------------------------------------------

###Usage:

**Loading library**

>
> $this->load->library('curl');
>

** Single query **

Just call ->get() method with URL as param for GET-request and ->post() with URL as first param and array with POST-data as second:

GET
> $this->curl->get('http://google.com/');

POST
> $data = array('foo'=>'bar','bla'=>'bla');
> $this->curl->post('http://google.com/',$data);

Functions will return response from cURL.

** Multi-threading **

If You need to request many pages, You can send array with URLs as first param to ->get() or ->post() function

GET
> $urls = array('http://google.com/','http://github.com');
> $this->curl->get($urls);

POST
> $urls = array('http://google.com/','http://github.com');
> $data = array('foo'=>'bar','bla'=>'bla');
> $this->curl->get($urls, $data);

Functions will return responses as array with keys from $urls. If You need to handle responses, just add keys to requested array:

> $urls = array('google'=>'http://google.com/','git'=>'http://github.com');

** Adding headers **

You can add custom HTTP headers or rewrite default values for Your requests by calling ->_setHeader() function:

> $this->curl->_setHeader('Host','example.com');

** etc **

There are some settings at top of class, please configure it what You like. I hope every options is commented well to understand, if not - please create an issue
