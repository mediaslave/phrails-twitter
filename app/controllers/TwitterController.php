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
			$tuser = $connection->get('account/verify_credentials');
		  	$user = new TwitterUser();
			try{
				$r = $user->conditions('twitter_id = ?', $tuser->id)->findAll(false);
				$user->props((array)$r);
			}catch(RecordNotFoundException $e){
				$user->twitter_id = $tuser->id;
			}	
			$user->token = $access_token['oauth_token'];
			$user->secret = $access_token['oauth_token_secret'];
			if($user->save()){
				$this->flash = 'Thank you for logging in with twitter.';
			}else{
				$this->flash = 'We have authenticated you with twitter, but we failed on our end.  We have notified the authorities. :(';
			}
			$_SESSION['pr-plugin-twitter-id'] = $user->id;
			
			//Now unset the access token.
			unset($_SESSION['access_token']);
			//Send the user to the homepage.
			$this->redirectTo(path('root'));
		} else {
			//print $connection->http_code;
		  /* Save HTTP status for error dialog on connnect page.*/
			$this->flash = 'We could not login to twitter.  Twitter reported error: ' . $connection->http_code;
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
	/**
	 * Logout the user
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function logout()
	{
		unset($_SESSION['pr-plugin-twitter-id']);
		$this->flash = 'We have logged you out.';
		$this->redirectTo(path('root'));
	}
}
