<?php
/*
Plugin Name:  Import Subscribers From Laravel Dashboard
Plugin URI:   #
Description:  A plugin to create subscribers from Laravel Dashboard/CRM.
Version:      1.0
Author:       Amisha Jain 
Author URI:   https://github.com/JainAmisha
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  import-subscribers-from-laravel-dashboard
*/

if(!defined('LARAVEL_WP_ENCRYPTION_DETAILS')){
    define('LARAVEL_WP_ENCRYPTION_DETAILS', 'laravel_wp_encryption_details');
}

/**
 * Main plugin class
 **/
class Import_Subscribers_From_Laravel_Dashboard 
{
	public function __construct()
    {
        $this->init();

        // Plugin settings page
        add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ), 10, 1 );
        
        // Extra meta in users api to add/show if the user is created via laravel site
        add_action( 'rest_api_init', array($this, 'add_additional_meta_to_users_api') );
        
        // Extra column in users table that links to laravel profile
        add_filter( 'manage_users_columns', array($this, 'custom_add_laravel_profile_column') );
        add_filter( 'manage_users_custom_column',  array($this, 'custom_show_laravel_profile_column_content'), 10, 3);

        // Apply basic authentication to apis
        add_filter( 'determine_current_user', array($this, 'custom_determine_current_user'), 20);
        add_filter( 'rest_authentication_errors', array($this, 'custom_rest_authentication_errors') );

        $this->basic_authentication_error = null;

	}

    public function init(){
        if( !class_exists('Import_Subscribers_Settings_Page') )
        {
            include_once 'includes/class-import-subscribers-settings-page.php';
        }
    }

	public static function plugin_action_links($links)
    {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=import-subscriber-settings').'" >' . __('Settings', 'import-subscribers-from-laravel-dashboard') . '</a>',
        );
        return array_merge( $plugin_links, $links );
	}
 
    public function add_additional_meta_to_users_api()
    {
        $field = 'laravel_profile_status';
        register_rest_field(
            'user', $field,
            array(
                'get_callback' => function ( $object ) use ( $field ){
                    return get_user_meta( $object['id'], $field, true );
                },
                'update_callback' => function ( $value, $object ) use ( $field ) {
                    update_user_meta( $object->ID, $field, $value );
                },
                'schema'          => array(
                    'type'        => 'boolean',
                    'arg_options' => array(
                        'sanitize_callback' => function ( $value ) {
                            return rest_sanitize_boolean( $value );
                        },
                        'validate_callback' => function ( $value ) {
                            return wp_validate_boolean($value);
                        },
                    ),
                ),
            )
        );
    }

    public function custom_add_laravel_profile_column( $column )
    {
        $column['laravel_profile'] = 'Laravel Profile';
        return $column;
    }

    public function custom_show_laravel_profile_column_content($value, $column_name, $user_id)
    {
        $user = get_userdata($user_id);
        $laravel_profile_status = get_user_meta( $user_id, 'laravel_profile_status', true );
        if ( is_object($user) && 'laravel_profile' == $column_name && $laravel_profile_status == 1)
        {
            $profile = $this->encrypt_string($user->user_email);
            $encryption_details = get_option(LARAVEL_WP_ENCRYPTION_DETAILS, 1);
            $laravel_customer_profile_url = isset($encryption_details['laravel_customer_profile_url']) ? $encryption_details['laravel_customer_profile_url'] : '#';
            return '<a href="'.$laravel_customer_profile_url.'/'.$profile.'" target="_blank">Laravel Profile</a>';
        }
        return $value;
    }

    public function custom_determine_current_user( $user )
    {
        $this->basic_authentication_error = null;
    
        if ( !empty( $user ) ) {
            return $user;
        }
    
        if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $user;
        }
    
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        // multisite looped calls
        remove_filter( 'determine_current_user', array($this, 'custom_determine_current_user'), 20 );
    
        $user = wp_authenticate( $username, $password );
    
        add_filter( 'determine_current_user', array($this, 'custom_determine_current_user'), 20 );
    
        if ( is_wp_error( $user ) ) {
            $this->basic_authentication_error = $user;
            return null;
        }
    
        $this->basic_authentication_error = true;
    
        return $user->ID;
    }

    public function custom_rest_authentication_errors( $error ) {
        // Passthrough other errors
        if ( ! empty( $error ) ) {
            return $error;
        }
        
        return $this->basic_authentication_error;
    }

    private function encrypt_string($string)
    {
        $encryption_details = get_option(LARAVEL_WP_ENCRYPTION_DETAILS, 1);

        $ciphering      = isset($encryption_details['ciphering']) ? $encryption_details['ciphering'] : '';
        $iv_length      = openssl_cipher_iv_length($ciphering);
        $encryption_iv  = isset($encryption_details['iv']) ? $encryption_details['iv'] : '';
        $encryption_key = isset($encryption_details['key']) ? $encryption_details['key'] : '';
        $encryption     = openssl_encrypt($string, $ciphering, $encryption_key, 0, $encryption_iv);
        $encryption     = base64_encode($encryption);

        return $encryption;
    }
   
}

new Import_Subscribers_From_Laravel_Dashboard();