<?php
class BaseController
{
  /**
  * __call magic method.
  */
  public function __call($name, $arguments) {
    $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
  }

  /**
  * Get URI elements.
  *
  * @return array
  */
  protected function getUriSegments() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);

    return $uri;
  }

  /**
  * Get querystring params.
  *
  * @return array
  */
  protected function getQueryStringParams() {
    parse_str($_SERVER['QUERY_STRING'], $query);
    return $query;
  }

  protected function getChatLimit($limit) {
    if (is_numeric($limit)) {
      return $limit;
    } else {
      return 10; //sets a default chat limit
    }
  }

  /**
  * Send API output.
  *
  * @param mixed  $data
  * @param string $httpHeader
  */
  protected function sendOutput($data, $httpHeaders = array()) {
    header_remove('Set-Cookie'); //prevent any form of caches
    header_remove();

    if (is_array($httpHeaders) && count($httpHeaders)) {
      foreach ($httpHeaders as $httpHeader) {
        header($httpHeader);
      }
    }

    echo $data;
    exit;
  }
}
?>