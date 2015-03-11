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
    private static $options;
    
    public static function init()
    {
        // Intercept redirect and execute logic code..
        add_action('template_redirect', 'ttd_zimbra_preauth::redirect' );
        // Register Widget
        add_action('widgets_init', create_function('', 'return register_widget("wp_zimbra_preauth");'));

        // Add Admin Area
        add_action('admin_init', 'ttd_zimbra_preauth::page_init');
        add_action('admin_menu', 'ttd_zimbra_preauth::add_plugin_page' );
    }

    public static function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Zimbra Pre-Auth Settings', 
            'Zimbra SSO',
            'manage_options', 
            'zimbra-preauth-settings', 
            'ttd_zimbra_preauth::settings_page'
        );
    }

    public static function page_init()
    {

        register_setting(
            'ttd_zimbra_preauth::option-group', // Option group
            'zimbrapreauth::login_url', // Option name
            'TTD_Zimbra_Preauth::sanitize'  // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'My Custom Settings', // Title
            'TTD_Zimbra_Preauth::print_section_info', // Callback
            'zimbra-preauth-settings' // Page
        );  

        add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            'TTD_Zimbra_Preauth::id_number_callback', // Callback
            'zimbra-preauth-settings', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'title', 
            'Title', 
            'TTD_Zimbra_Preauth::title_callback', 
            'zimbra-preauth-settings', 
            'setting_section_id'
        );      
    
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public static function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public static function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function id_number_callback()
    {
        printf(
            '<input class="regular-text code" type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
            isset( self::$options['id_number'] ) ? esc_attr(  self::$options['id_number']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function title_callback()
    {
        printf(
            '<input class="regular-text code" type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset(  self::$options['title'] ) ? esc_attr(  self::$options['title']) : ''
        );
    }

    public static function settings_page()
    { ?>
        <div class="wrap">
            <h2>Zimbra PreAuth Settings</h2>
            <form method="post" action="options.php"> 
            <?php settings_fields( 'ttd_zimbra_preauth::option-group' ); ?>
            <?php do_settings_sections( 'zimbra-preauth-settings' ); ?>
            <?php submit_button(); ?>
            </form>
        </div>
    <?php }

    // Intercept and redirect
    public static function redirect()
    {
        global $wp_query, $post;

        if( $wp_query->query_vars['pagename'] == 'zibra_pre_auth' 
                AND $wp_query->is_404 )
        { 

            if ( !is_user_logged_in() ){
                $login_url = get_option( 'zimbrapreauth::login_url' );
                header("Location: $login_url");
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