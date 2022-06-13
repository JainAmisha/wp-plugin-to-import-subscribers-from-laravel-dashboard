<?php

if(!class_exists('Import_Subscribers_Settings_Page'))
{
    class Import_Subscribers_Settings_Page 
    {
        public function __construct()
        {
            add_action(	'admin_menu', array( $this, 'add_admin_submenu' ) );
            if(isset($_POST['laravel_wp_encryption_details']))
            {
                $this->laravel_wp_encryption_settings($_POST);
            }
        }

        public function add_admin_submenu()
        {
            add_users_page( 
                __('Import Subscriber Settings', 'import-subscribers-from-laravel-dashboard'),
                __('Import Subscriber Settings', 'import-subscribers-from-laravel-dashboard'),
                'create_users',
                'import-subscriber-settings',
                array( $this, 'import_subscribers_settings_page' ) 
            );
        }

        public function import_subscribers_settings_page()
        {
            if ( !current_user_can( 'create_users' ) ){
                wp_die( __( 'You do not have sufficient permissions to access this page.' , 'import-subscribers-from-laravel-dashboard') );
            }

            include_once('html-import-subscribers-settings-page.php');
        }

        public function laravel_wp_encryption_settings($post)
        {
            $settings = [
                'ciphering'         => $post['encryption_cipher'],
                'iv'                => $post['encryption_iv'],
                'key'               => $post['encryption_key'],
                'laravel_customer_profile_url' => $post['laravel_customer_profile_url']
            ];

            update_option(LARAVEL_WP_ENCRYPTION_DETAILS, $settings);
        }

    } new Import_Subscribers_Settings_Page();
}