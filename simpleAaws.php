<?php
/*
Plugin Name: SimpleAAWS
Plugin URI: http://affilimania.net
Description: This plugin integrates SimpleAAWS (for Amazon Associates Web Service) from http://affilimania.net into wordpress as a sidebar widget
Version: 1.0
Author: affilimania.net
Author URI: http://affilimania.net
*/

add_action ( 'init', 'init_simpleaaws');

function init_simpleaaws()
{
  global $aawsView;
  
  $path = get_option('simpleaaws_setting_path');
  
  if(is_file($path  . '/' . 'application' .'/'. 'initSimpleAAWS.php'))
	{
	  if(!defined("AAWS_BASE_DIR"))
	    define("AAWS_BASE_DIR", $path);
	     
    require_once(AAWS_BASE_DIR  . '/' . 'application' .'/'. 'initSimpleAAWS.php');
  
    $aawsView = new AawsView();
    
    // set a fixed view 
    //$aawsView->setDefaultView('aawsarticles');
    //$aawsView->setFixedSearchIndex('PCHardware');
    //$aawsView->setDefaultBrowseNode('700962');
    
    $aawsView->setImagePath('wp-content/plugins/simpleaaws-widget-plugin/images');
    $aawsView->setJavascriptPath('wp-content/plugins/simpleaaws-widget-plugin/javascript');
    
    $wp = new WP();
    
    $extArgs = array();
    foreach ($wp->public_query_vars as $varIdx => $queryVar)
    {
      if(isset($_GET[$queryVar]))
        $extArgs[$queryVar] = $_GET[$queryVar];
        
      if(isset($_POST[$queryVar]))
        $extArgs[$queryVar] = $_POST[$queryVar];
    }          
    $aawsView->setExternalArgs($extArgs); 
    
    $aawsView->setSindexVariable('sindex');
    $aawsView->setPageVariable('aawsp');
    
    $aawsView->runController();
  }
	else 
	{
	  // SimpleAAWS not installed correctly or SimpleAAWS path not set correctly in the administration area !!!
	  return false;
	}
}

function simpleaaws_sidebar_init() 
{
  global $aawsView;

  if ( !function_exists('wp_register_sidebar_widget') )
    return;

  // Ausgabe Frontend
  function simpleaaws_sidebar($args) 
  {
    global $aawsView;
    extract($args);
    
    // Auslesen der Optionen
    $options = get_option('simpleaaws_sidebar');
    $title = htmlspecialchars($options['title'], ENT_QUOTES);

    // Ausgabe des Widgets
    echo $before_widget . $before_title . $title . $after_title;
    //echo print_r($aawsView, 1);
    
    if(is_object($aawsView))
      $aawsView->renderView('mini');
    else 
      echo "SimpleAAWS not installed correctly or SimpleAAWS path not set correctly in the administration area !!!";
    
    echo $after_widget;
  }

  // back end controller
  function simpleaaws_sidebar_control() 
  {
    // Auslesen der Optionen
    $options = get_option('simpleaaws_sidebar');
    
    // Wenn Optionen nicht angegeben, Default-Werte setzen
    if ( !is_array($options) )
    $options = array('title'=>'Simple AAWS Categories Widget');

    if ( $_POST['simpleaaws_sidebar-submit'] ) {
      $options['title'] = strip_tags(stripslashes($_POST['simpleaaws_sidebar-title']));
      update_option('simpleaaws_sidebar', $options);
    }

    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    
    echo '
<p style="text-align:right;"><label for="simpleaaws_sidebar-title">Title
<input style="width: 150px;" id="simpleaaws_sidebar-title" name="simpleaaws_sidebar-title" type="text" value="' . $title . '" /></label>
 
';
    
    echo '
<input type="hidden" id="simpleaaws_sidebar-submit" name="simpleaaws_sidebar-submit" value="1" />';
  }

  wp_register_sidebar_widget('simpleaaws_sidebar', 'SimpleAAWS',
    'simpleaaws_sidebar',
    array(
      'classname' => 'simpleaaws_sidebar',
      'description' =>'Show the Simple AAWS plugin' ) );
  
  wp_register_widget_control('simpleaaws_sidebar', 'SimpleAAWS',
    'simpleaaws_sidebar_control',
    array( 'width' => 250  ) );

}

add_action('widgets_init', 'simpleaaws_sidebar_init');

// ------------------------------------------------------------------
// Admin init section
// ------------------------------------------------------------------
function simpleaaws_settings_api_init() {
	// Add the section to general settings
	add_settings_section('simpleaaws_setting_section', 'SimpleAAWS settings section', 'simpleaaws_setting_section_callback_function', 'general');
	
	// Add the field with the names and function 
	add_settings_field('simpleaaws_setting_path', 'SimpleAAWS Path', 'simpleaaws_setting_callback_function', 'general', 'simpleaaws_setting_section');
	
	// Register our setting so that $_POST handling is done
	register_setting('general','simpleaaws_setting_path');
}

add_action('admin_init', 'simpleaaws_settings_api_init');

// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
function simpleaaws_setting_section_callback_function() {
	echo "<p>In order to run this plugin with SimpleAAWS you have to download and install SimpleAAWS from <a href=\"http://affilimania.net\" target=\"_blank\">affilimania.net</a> separately. Just unpack the downloaded package, edit the configuration file, upload it to a publically not available directory of your webserver and enter the directory path here. The path should look like this (no slashes at the end): /usr/local/SimpleAAWS</p>";
}

// ------------------------------------------------------------------
// Settings callback functions
// ------------------------------------------------------------------
function simpleaaws_setting_callback_function() {
?>
<input name="simpleaaws_setting_path" type="text" id="simpleaaws_setting_path " value="<?php form_option('simpleaaws_setting_path'); ?>" class="regular-text" />
<br /> 
<?php
  if(is_file(get_option('simpleaaws_setting_path')  . '/' . 'application' .'/'. 'initSimpleAAWS.php'))
  {
    echo "<font color='green'>SimpleAAWS path OK</font>";
  }
  else echo "<font color='red'>SimpleAAWS path not set correctly !!!</font>";

} 