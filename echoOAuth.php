<?php
/**
 * Echo OAuth class
 */
class EchoOAuth {/*{{{*/
  /* Contains the last HTTP status code returned */
  private $http_status;

  /* Contains the last API call */
  private $last_api_call;

  public $last_header;

  /* Set up the API root URL */
  public static $TO_API_ROOT = "http://api.echoenabled.com/v1";

  /**
   * Set API URLS
   */
  function requestTokenURL() { return self::$TO_API_ROOT.'/oauth/request_token'; }
  function authorizeURL() { return self::$TO_API_ROOT.'/oauth/authorize'; }
  function accessTokenURL() { return self::$TO_API_ROOT.'/oauth/access_token'; }

  /**
   * Debug helpers
   */
  function lastStatusCode() { return $this->http_status; }
  function lastAPICall() { return $this->last_api_call; }

  /**
   * construct OAuth object
   */
  function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {/*{{{*/
	$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
	$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	if (!empty($oauth_token) && !empty($oauth_token_secret)) {
	  $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
	} else {
	  $this->token = NULL;
	}
  }/*}}}*/

  /**
   * Format and sign an OAuth / API request
   */
  function oAuthRequest($url, $args = array(), $method = NULL) {/*{{{*/
	if (empty($method)) $method = empty($args) ? "GET" : "POST";
	$req = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $args);
	$req->sign_request($this->sha1_method, $this->consumer, $this->token);
	switch ($method) {
	case 'GET': return $this->http($req->to_url());
	case 'POST': return $this->http($req->get_normalized_http_url(), $req->to_postdata());
	}
  }/*}}}*/

  /**
   * Make an HTTP request
   *
   * @return API results
   */
  function http($url, $post_data = null) {/*{{{*/
	$ch = curl_init();
	if (defined("CURL_CA_BUNDLE_PATH")) curl_setopt($ch, CURLOPT_CAINFO, CURL_CA_BUNDLE_PATH);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//////////////////////////////////////////////////
	///// Set to 1 to verify SSL Cert           //////
	//////////////////////////////////////////////////
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if (isset($post_data)) {
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	$response = curl_exec($ch);
	$this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$this->last_api_call = $url . $post_data;
	curl_close ($ch);
	return $response;
  }/*}}}*/
}/*}}}*/

function method_user_update($consumer_key, $consumer_secret, $identityURL=null, $subject, $content) {
	$to = new EchoOAuth($consumer_key, $consumer_secret);
	$rsp = $to->OAuthRequest('http://api.echoenabled.com/v1/users/update', array('identityURL'=>$identityURL,'subject'=>$subject,'content'=>$content), 'POST');
	return $rsp;
}

//echo $result;
//die();
?>