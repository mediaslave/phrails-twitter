<?php

class TwitterController extends ApplicationController
{
	//You Can change the layout here. 'application' is default.
	//Change to null to have no layout.
	//protected $pr_layout = 'my-cool-layout';
	public $pr_view_path = TWITTER_PLUGIN_VIEWS;
	
	/**
	 * Redirect the user to twitter to get the user authenticated.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function redirect()
	{
		$connection = new PhrailsTwitter();

		/* Get temporary credentials. */
		$request_token = $connection->getRequestToken();

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code) {
		  case 200:
		     //Build authorize URL and redirect user to Twitter.
		    $url = $connection->getAuthorizeURL($token);
		    header('Location: ' . $url); 
		    break;
		  default:
		    $this->render('offline');
		}
	}
	
	/**
	 * Callback for twitter authentication
	 *
	 * 	@todo if the first if fails redirect to root_path and give them a flash
	 * 
	 * @return void
	 * @author Justin Palmer
	 **/
	public function callback()
	{
		
		/* If the oauth_token is old redirect to the connect page. */
		if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		  	$_SESSION['oauth_status'] = 'oldtoken';
			//redirect to root_path and give them a flash
			$this->redirectTo('/');
		}

		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new PhrailsTwitter($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$_SESSION['access_token'] = $access_token;
		

		/* Remove no longer needed request tokens */
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if (200 == $connection->http_code) {
		  /* The user has been verified and the access tokens can be saved for future use */
		  $_SESSION['status'] = 'verified';
			print 'verified';
			$this->redirectTo(path('root'));
		} else {
			print $connection->http_code;
		  /* Save HTTP status for error dialog on connnect page.*/
			$this->redirectTo(path('root'));
		}
	}
	
	/**
	 * If twitter is offline then we will show this view.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function offline()
	{
		
	}
}
