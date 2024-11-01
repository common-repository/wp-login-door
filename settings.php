<?php

defined( 'ABSPATH' ) or die( 'Oh well...' );

require_once(ABSPATH . '/wp-admin/includes/template.php');
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . WPINC . '/pluggable.php');

/**
 * Manages the plugin settings
 */
class WpLoginDoorSettings
{
  private $version = "1.5";
  private $settingsPageName = 'wp-door-configuration';

  public $options = array();

  /**
   * Builds the Settings object, ie properties accessor + settings interface.
   * If $onlyOptions == true, no user interface is built.
   */
  public function __construct($onlyOptions = false) {
    //Loads all the settings (default values if needed)
    $this->initOptionFields();

    if($onlyOptions)
      return;

    add_action('admin_init', array($this, 'admin_init'));
    add_action( 'admin_menu', array($this, 'add_admin_menu' ));

    //settings link from the main plugin list
    $pluginPath = "wplogindoor/wp-login-door.php";
    add_filter("plugin_action_links_$pluginPath", array($this, 'create_settings_link' ));

    //show the notice when the versions are different or version number doesn't exist in the database yet.
    $storedVersion = get_option('wp-door-version');
    if(!$storedVersion)
    {
      add_action( 'admin_notices', array($this, 'after_install_notice') );
      return;
    }
    if(version_compare($storedVersion, $this->version) < 0)
    {
      add_action( 'admin_notices', array($this, 'after_update_notice') );
      return;
    }

  }

  /**
   * Initializes the option list
   */
  private function initOptionFields() {

    $this->options[] = array('name'=>'wp-door-keyname', 'type'=>'text', 'label'=>'Key name', 'callback'=>array($this, 'field_callback'), 'default'=>'_key', 'sanitization'=>array($this, 'sanitize_key_callback'));
    $this->options[] = array('name'=>'wp-door-keyvalue', 'type'=>'text', 'label'=>'Key value', 'callback'=>array($this, 'field_callback'), 'default'=>'_value', 'sanitization'=>null);
    $this->options[] = array('name'=>'wp-door-errormessage', 'type'=>'text', 'label'=>'Error message', 'callback'=>array($this, 'field_callback'), 'default'=>'error', 'sanitization'=>null);
    $this->options[] = array('name'=>'wp-door-disable-xml-rpc', 'type'=>'bool', 'label'=>'Disable XML RPC', 'callback'=>array($this, 'field_callback'), 'default'=>'on', 'sanitization'=>null);
    $this->options[] = array('name'=>'wp-door-redirect-home', 'type'=>'bool', 'label'=>'Redirect to home page on error', 'callback'=>array($this, 'field_callback'), 'default'=>'off', 'sanitization'=>null);

    //initialize values from database
    for ($i=0;$i<count($this->options);$i++)
    {
      $opt = $this->options[$i];
      $this->options[$i]['value'] = get_option($this->options[$i]['name'], $this->options[$i]['default']);
    }
  }

  /**
   * Returns the value of an option
   */
  public function getOption($optionName) {
    foreach ($this->options as $opt)
    {
      if($opt['name'] == $optionName)
        return $opt['value'];
    }
    return null;
  }

  /**
   * Creates the "settings" link
   */
  function create_settings_link($links) {
    $settings_link = '<a href="options-general.php?page='.$this->settingsPageName.'">Settings</a>';
    $links[] = $settings_link;

    return $links;
  }

  function after_install_notice() {

    echo '<div class="notice notice-success is-dismissible">';
    echo '    <p>Thank you for using Wp Login Door!</p>';
    echo '    <p>Please go to the <a href="options-general.php?page='. $this->settingsPageName.'">settings page</a> before it\'s too late and you can\'t login anymore :D</p>';
    echo '</div>';

  }

  function after_update_notice() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '    <p>Wp Login Door upgrade successful! Current version is now '.$this->version.'</p>';
    echo '    <p>Please go to the <a href="options-general.php?page='. $this->settingsPageName.'">settings page</a> to check new features.</p>';
    echo '</div>';
  }

  /*function after_activation() {
    add_action( 'admin_notices', array($this, 'after_activation_notice') );
  }*/

  /**
   * Add the option menu
   */
  function add_admin_menu() {
    add_options_page( 'WpLoginDoor configuration', 'WpLoginDoor', 'manage_options', $this->settingsPageName, array($this, 'show_options_page'));

  }

  /**
   * Initialize the options for the settings form.
   */
  function admin_init() {

    add_settings_section( 'default', 'WpLoginDoor settings (v.'.$this->version.')', array($this, 'sectionCallback'), $this->settingsPageName);

    foreach($this->options as $opt)
    {
      add_settings_field($opt['name'], $opt['label'], $opt['callback'], $this->settingsPageName, 'default', array($opt['name'], $opt['type']));
      register_setting( 'default', $opt['name'], $opt['sanitization']);
    }
  }

  /**
   * Configuration page
   */
  function show_options_page() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    echo '<div class="wrap">';
    $this->displayForm();
    echo '</div>';
  }

  function sectionCallback() {
    $goodUrl = home_url()."/wp-login.php?".$this->getOption("wp-door-keyname")."=".$this->getOption("wp-door-keyvalue");
    $badUrl = home_url()."/wp-login.php";
    echo "<p>Your login url is now : <a target=\"_blank\" href=\"$goodUrl\">$goodUrl</a></p>";
    echo "<p>Please click on this link and ensure you see the login page! Then you can bookmark it if you feel like.</p>";
    echo "<p>You can also see what prohibited users will see if they don't provide the key : <a target=\"_blank\" href=\"$badUrl\">$badUrl</a></p>";
  }

  function field_callback($param_arr) {

    $name = $param_arr[0];
    $type = $param_arr[1];

    switch($type) {
      case 'text':
        echo '<input name="'.$name.'" id="'.$name.'" type="text" value="'.$this->getOption($name).'" class="code" />';
        break;

      case 'bool':
        $checked = $this->getOption( $name ) == "on" ? 'checked="checked"' : '';
        echo '<input name="'.$name.'" id="'.$name.'" type="checkbox" '.$checked.' class="code" />';
        break;
    }

  }

  function sanitize_key_callback($input) {
    if(is_numeric($input))
    {
      add_settings_error('wp-door-keyname', 'invalid-numeric', 'Invalid key name : cannot be a number');
      return "_$input";
    }

    $reserved_words = array("key", "error", "login", "loggedout", "registration", "checkemail");

    if(array_search($input, $reserved_words) !== FALSE)
    {
      add_settings_error('wp-door-keyname', 'invalid-keyname', "Invalid key name : $input is a reserved word");
      return "_$input";
    }

    return $input;
  }

  public function displayForm(){

    //updates the version number in the database as soon as we display the config page,
    //to ensure the user knows what he does
	  update_option('wp-door-version', $this->version);

    //display the form
    echo '<form method="POST" action="options.php">';
    settings_fields('default');  //section settings

    do_settings_sections( $this->settingsPageName );   //pass slug name of page

    submit_button();

    echo '</form>';

  }
}

$settings = new WpLoginDoorSettings();
