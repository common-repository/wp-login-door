<?php
/*
Plugin Name: WP Login Door
Plugin URI:  http://dirtymarmotte.net/
Description: Restricts the access to your Wordpress login page with a secret key and disables XMLRPC
Version:     1.5
Author:      Nicolas Simonnet
Author URI:  http://dirtymarmotte.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Oh well...' );

require_once(dirname(__FILE__)."/settings.php");

class WpLoginDoor
{
  private $settings;

  public function __construct()
  {
    //settings initialization
    $this->settings = new WpLoginDoorSettings(true);

    //Overrides the login page
    add_action('login_init', array($this, 'display_login_form'));

    add_action('login_form', array($this, 'add_hidden_field'));

    //Ensure the login key was provided in POST login requests
    add_action('wp_authenticate', array($this, 'before_authenticate'));

    //enables or disables the xml-rpc feature,
    //which is a very popular bruteforce attack vector
    add_filter('xmlrpc_enabled', array($this, 'xmlrpc_filter'));

  }

  /**
   * Checks the presence and validity of the login key
   * before displaying the login form
   */
  function display_login_form()
  {
    //if we're sending login and password, let it go
    //it means that the form has already been displayed,
    //and thus our login key has been validated
		if(!empty($_REQUEST['log']))
			return;

    //Some standard Wordpress actions must be authorized.
    //For example if we're logging out, let it go.
    //postpass is used when user wants to show a password-protected post.
    $authorizedActions = array('logout', 'postpass');
		if(isset($_REQUEST['action']) && in_array($_REQUEST['action'], $authorizedActions))
			return;


		//Check if our key is provided in the query string
		if(isset($_REQUEST[$this->settings->getOption('wp-door-keyname')]) && $_REQUEST[$this->settings->getOption('wp-door-keyname')] == $this->settings->getOption('wp-door-keyvalue'))
      return;

    //Finally, display the error page
		die($this->getErrorPage());

  }

  /**
   * This adds a hidden field in the login form. It will be sent along login/password  and checked before authentication
   */
  public function add_hidden_field()
  {
    echo '<input type="hidden" name="'.$this->settings->getOption('wp-door-keyname').'" value="'.$this->settings->getOption('wp-door-keyvalue').'"/>';
  }

  /**
   * This method is called before the user is authenticated.
   * Here we ensure the key was provided from the login form.
   * Else, anyone could just send a POST request and attempt to login
   * without using the login form. That's what robots do, anyway.
   */
  function before_authenticate()
  {

    //if we're not sending the login form, let it go
		if(empty($_REQUEST['log']))
			return;

    //is a key supplied?
    if(!isset($_REQUEST[$this->settings->getOption('wp-door-keyname')]))
      die($this->getErrorPage());

    //check the validity of posted key
		if($_REQUEST[$this->settings->getOption('wp-door-keyname')] != $this->settings->getOption('wp-door-keyvalue'))
			die($this->getErrorPage());

  }

  /**
   * Enables or disables the XML-RPC feature.
   * Returns false if xmlrpc is disabled.
   */
  function xmlrpc_filter()
  {
    return !$this->settings->getOption('wp-door-disable-xml-rpc');
  }

  /**
   * Returns the error message
   */
  function getErrorPage()
  {
    if($this->settings->getOption('wp-door-redirect-home') == 'on')
      header("Location: ".home_url());
    else
      return $this->settings->getOption("wp-door-errormessage");
  }
}

new WpLoginDoor();
