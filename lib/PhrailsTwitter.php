<?php

/**
* Extension of twitteroauth to have the config pre set
*/
class PhrailsTwitter extends TwitterOAuth	
{
	private $callback;
	function __construct($oauth_token = NULL, $oauth_token_secret = NULL)
	{
		$ini = Registry::get('pr-plugin-twitter');
		$this->callback = $ini->callback;
		parent::__construct($ini->key, $ini->secret, $oauth_token, $oauth_token_secret);
	}
	/**
	 * @see parent
	 */
	public function getRequestToken() {
		return parent::getRequestToken($this->callback);
	}
}
