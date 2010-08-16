# Introduction

This is a plugin that allows you to use phrails to authenticate with twitter.

# Installation

This plugin relies on twitteroauth.  It must be cloned in lib/abraham

    http://github.com/abraham/twitteroauth/

# Setup

There are a couple of items to set up.

### Controller

	class ApplicationController extends Controller
	{
		//You Can change the layout here. 'application' is default.
		//Change to null to have no layout.
		//protected $pr_layout = 'my-cool-layout';
	
		/**
		 * Setup filters
		 *
		 * @return void
		 * @author Justin Palmer
		 **/
		public function __construct()
		{
			parent::__construct();
			$this->filters()->before('authenticate');
		}
	
		/**
		 * Authenticate the user so that there is a connection and a user.
		 *
		 * @return void
		 * @author Justin Palmer
		 **/
		public function authenticate()
		{	
			$this->user = null;
			$this->tuser = null;
			try{
				if($this->params('pr-plugin-twitter-id') === null)
					throw new Exception();
				$this->user = new TwitterUser();

				$r = $this->user->find($this->params('pr-plugin-twitter-id'));

				$this->user->props((array)$r);

				$connection = new PhrailsTwitter($this->user->token, $this->user->secret);

				$this->tuser = $connection->get('account/verify_credentials');
			}
			/*If we can't find the record or there is no session for this user.  Fall out.*/
			catch(RecordNotFoundException $e){}
			catch(Exception $e){}
		}
	
	}

### Routes

	$Routes->add('twitter-redirect', '/twitter/redirect', 'Twitter', 'redirect');
	$Routes->add('twitter-callback', '/twitter/callback', 'Twitter', 'callback');
	$Routes->add('twitter-offline', '/twitter/offline', 'Twitter', 'offline');
	$Routes->add('twitter-logout', '/twitter/logout', 'Twitter', 'logout');