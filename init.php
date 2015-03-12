<?php
/**
 * Plugin Name: Zimbra Preauth Widget
 * Plugin URI: https://github.com/geraintp/wp-zimbra-preauth
 * Description: A brief description of the plugin.
 * Version: 1.0.0
 * Author: Geraint Palmer
 * Author URI: http://twothirdsdesign.co.uk
 * License: GPL2
 */

/**
* 
*/
class TTD_Zimbra_Preauth
{   
    public static function init()
    {
        // Intercept redirect and execute logic code..
        add_action('template_redirect', 'ttd_zimbra_preauth::redirect' );
        // Register Widget
        add_action('widgets_init', create_function('', 'return register_widget("wp_zimbra_preauth");'));

        if( is_admin() )
            $settings_page = TTD_Zimbra_Preauth_Admin_Settings::init();
    }

    // Intercept and redirect
    public static function redirect()
    {
        global $wp_query, $post;

        $options = get_option("zimbrapreauth");

        if( $wp_query->query_vars['pagename'] == 'zibra_pre_auth' 
                AND $wp_query->is_404 )
        { 

            if ( !is_user_logged_in() ){
                $login_url = get_option( 'zimbrapreauth::login_url' );
                header("Location: {$options['login_url']}");
                exit;
            }

            $preauth_key = get_option( 'zimbrapreauth::preauth_key' ); 
            $web_mail_preauth_url = get_option( 'zimbrapreauth::web_mail_preauth_url' );


            $current_user = wp_get_current_user();   
            $email = $current_user->user_email;

            if(empty($preauth_key)) {
                die("Need preauth key for domain ".$domain);
            }

            /**
            * Create preauth token and preauth URL
            */
            $timestamp=time()*1000;
            $preauthToken=hash_hmac("sha1",$email."|name|0|".$timestamp,$preauth_key);
            $preauthURL = $web_mail_preauth_url."?account=".$email."&by=name&timestamp=".$timestamp."&expires=0&preauth=".$preauthToken;

            /**
             * Redirect to Zimbra preauth URL
             */
            header("Location: $preauthURL");
        }
    }
}

/**
* 
*/
class TTD_Zimbra_Preauth_Admin_Settings
{
    private static $_options = false;
    private static $_instance = false;

    // Page Slug ID
    const PAGEID = 'zimbra-preauth-settings';
    
    /**
     * Constructon adds hooks that load the admin page.
     *
     * @return void
     **/
    private function __construct()
    {
        // Add Admin Area
        add_action('admin_init', 'ttd_zimbra_preauth_admin_settings::page_init');
        add_action('admin_menu', 'ttd_zimbra_preauth_admin_settings::add_plugin_page' );
    }

    /**
     * Singlton
     *
     * @return instance
     * @static 
     **/
    public static function init()
    {
        if ( ! self::$_instance )
            self::$_instance = new TTD_Zimbra_Preauth_Admin_Settings;

        return self::$_instance;
    }


    public static function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Zimbra Pre-Auth Settings', 
            'Zimbra SSO',
            'manage_options', 
            self::PAGEID, 
            'ttd_zimbra_preauth_admin_settings::settings_page'
        );
    }

    public static function page_init()
    {
        self::$_options = get_option("zimbrapreauth");

        register_setting(
            'ttd_zimbra_preauth::option-group', // Option group
            'zimbrapreauth', // Option name
            'ttd_zimbra_preauth_admin_settings::sanitize'  // Sanitize
        );

        add_settings_section(
            'zimbra_preauth_setting_section', // ID
            '', // Title
            'ttd_zimbra_preauth_admin_settings::print_section_info', // Callback
            self::PAGEID // Page
        );  



        add_settings_field(
            'preauth_key', // ID
            'Zimbra PreAuth Key', // Title 
            'ttd_zimbra_preauth_admin_settings::preauth_key_callback', // Callback
            self::PAGEID, // Page
            'zimbra_preauth_setting_section' // Section           
        );

        add_settings_field(
            'login_url', // ID
            'Login URL', // Title 
            'ttd_zimbra_preauth_admin_settings::login_url_callback', // Callback
            self::PAGEID, // Page
            'zimbra_preauth_setting_section' // Section           
        );      

        // add_settings_field(
        //     'title', 
        //     'Title', 
        //     'ttd_zimbra_preauth_admin_settings::title_callback', 
        //     'zimbra-preauth-settings', 
        //     'setting_section_id'
        // );      
    
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public static function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['preauth_key'] ) )
            $new_input['preauth_key'] = sanitize_text_field( $input['preauth_key'] );

        if( isset( $input['login_url'] ) )
            $new_input['login_url'] = sanitize_text_field( $input['login_url'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public static function print_section_info()
    {
        //print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function preauth_key_callback()
    {
        printf(
            '<input class="regular-text code" type="text" id="preauth_key" name="zimbrapreauth[preauth_key]" value="%s" />',
            isset( self::$_options['preauth_key'] ) ? esc_attr( self::$_options['preauth_key'] ) : ''
        );
        print "<p class=\"description\">Please enter Zimbra PreAuth Key</p>";
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function login_url_callback()
    {
        var_dump(self::$_options);
        printf(
            '<input class="regular-text code" type="text" id="login_url" name="zimbrapreauth[login_url]" value="%s" />',
            isset( self::$_options['login_url'] ) ? esc_attr( self::$_options['login_url'] ) : ''
        );
        print "<p class=\"description\">Enter Zimbras Url (e.g https://zimbra.server.com/)</p>";
    }

    /** 
     * Get the settings option array and print one of its values
     */
    // public static function title_callback()
    // {
    //     printf(
    //         '<input class="regular-text code" type="text" id="title" name="my_option_name[title]" value="%s" />',
    //         isset(  self::$_options['title'] ) ? esc_attr(  self::$_options['title']) : ''
    //     );
    // }

    public static function settings_page()
    { ?>
        <div class="wrap">
            <h2>Zimbra PreAuth Settings</h2>
            <form method="post" action="options.php"> 
            <?php settings_fields( 'ttd_zimbra_preauth::option-group' ); ?>
            <?php do_settings_sections( self::PAGEID ); ?>
            <?php submit_button(); ?>
            </form>
        </div>
    <?php }
}

TTD_Zimbra_Preauth::init();

/**
 * Example Widget Class
 */
class wp_zimbra_preauth extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function wp_zimbra_preauth() {
        parent::WP_Widget(false, $name = 'Zimbra Preauth', array( 'title' => null) );	
    }
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {	
        extract( $args );

        $title 	 = apply_filters('widget_title', $instance['title']);
        $message = get_option('zimbra_preauth::msg');
        ?>
          <?php echo $before_widget; ?>
              <?php if ( !empty($title) )
                    echo $before_title . $title . $after_title; ?>
					
					<a href="/?pagename=zibra_pre_auth"><?php echo $message; ?></a>
                    <a href="/?pagename=zibra_pre_auth">test</a>
					
          <?php echo $after_widget; ?>
        <?php
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {	

        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    }
 
} // end class example_widget
?>