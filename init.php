<?
$dirname = dirname(__FILE__);
define('TWITTER_PLUGIN_VIEWS', $dirname . '/app/views');
//Include all of the folders that need to be included to run the plugin.
add_include_directory($dirname . '/app/controllers');
add_include_directory($dirname . '/app/models');
add_include_directory($dirname . '/lib');
add_include_directory($dirname . '/lib/abraham/twitteroauth');

spl_autoload_register('lowerCaseAutoload');

/**
 * Lowercase autoload to deal with the twitteroauth weird class/file naming
 *
 * @return void
 * @author Justin Palmer
 **/
function lowerCaseAutoload($class)
{
	require_once(strtolower($class) . '.php');
}