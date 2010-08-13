<?
$dirname = dirname(__FILE__);
//Include all of the folders that need to be included to run the plugin.
add_include_directory($dirname . '/lib/twitteroauth');

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