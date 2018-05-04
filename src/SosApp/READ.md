$this->browser->userAgent           Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:59.0) Gecko/20100101 Firefox/59.0
$this->browser->isMobile            0 | 1
$this->browser->isTablet            0 | 1
$this->browser->isDesktop           0 | 1
$this->browser->screenHeight        0
$this->browser->screenWidth         0

$this->app->timezone
$this->app->setTimezone("Asia/Hong_Kong")
$this->app->getTimezone();
$this->app->dump(FALSE);

$this->cookie->set($name, $value, $expire);
$this->cookie->remove($name);
$this->cookie[$name]

$this->header->set($name, $value);
$this->header->status(404);
$this->header->get('Content-Type');
$this->header['CONTENT_TYPE']

$this->request[$name]
$this->request['__REQ_DATETIME__']
$this->request['__REQ_DATE__']
$this->request['__REQ_TIME__']
$this->request['__DATETIME__']
$this->request['__DATE__']
$this->request['__TIME__']
$this->request['__IP__']
$this->request['__AUTH_UESR__']
$this->request['__AUTH_PASS__']


$this->request->uri                 /dump.html/param1/param2
$this->request->timestamp           1524106899
$this->request->timestampFloat      1524106899.373
$this->request->datetime            2018-04-19 11:01:39 (request time)
$this->request->ip                  118.140.252.234 (remote ip)
$this->request->port                60466 (connect port)
$this->request->method              get | post ... (in lowercase)
$this->request->defaultLanguage     en (set up from config)
$this->request->language            en (parsed language)
$this->request->acceptEncoding      [gzip, deflate] / parsed from browser
$this->request->user                Basic Auth Username
$this->request->password            Basic Auth Password

$this->url->https           0 | 1
$this->url->scheme          http | https
$this->url->host            xxx.com
$this->url->schemehost      http://xxx.com
$this->url->dir             /
$this->url->basename        dump.html   (file name with extension)
$this->url->filename        dump        (file name without extension)
$this->url->extension       html
$this->url->path            /dump.html  (self path without params)

$this->language (.....)


$this->datetime->date(...)  time() + format
echo $this->datetime        request time with format: Y-m-d H:i:s

