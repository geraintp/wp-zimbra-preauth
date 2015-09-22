<?php
/**
 * Plugin Name: Zimbra Preauth Widget
 * Plugin URI: https://github.com/geraintp/wp-zimbra-preauth
 * Description: This plugin adds a simple link widget for Zibra Pre authentication
 * Version: 0.1.2
 * Author: @geraintp - Two Thirds Design
 * Author URI: http://www.twothirdsdesign.co.uk
 * License: GPL2
 */
defined( 'ABSPATH' ) or die( 'not found' );

define( 'ZIPA_MINIMUM_WP_VERSION', '4.0' );
define( 'ZIPA_VERSION', '0.1.0' );
define( 'ZIPA_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'ZIPA_PLUGIN_FILE', __FILE__ );

/**
*  Main Class
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
                header("Location: {$options['login_url']}");
                exit;
            }

            $preauth_key = $options['preauth_key'];
            $web_mail_preauth_url = $options['preauth_url'];


            $current_user = wp_get_current_user();   
            $email = $current_user->user_email;

            if(empty($preauth_key)) {
                die("Preauth key for domain not set!!");
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
            exit;
        }
    }
}

/**
* Admin Settings Page
*/
class TTD_Zimbra_Preauth_Admin_Settings
{
    private static $_options = false;
    private static $_default = array(
        'preauth_key' => '',
        'preauth_url' => '',
        'login_url' => '',
        'link_text' => '',
    );

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
        //doesnt work? quite right
        self::$_default['link_text'] = htmlentities(stripslashes('<div style="text-align: center;"> <img width="168" vspace="0" hspace="0" height="43" border="0" src="'. plugins_url( 'img/ZimbraLogo.png', ZIPA_PLUGIN_FILE ) .'" alt="School Email" title="School Email" /></div>'));
        
        // Add Admin Area
        add_action('admin_init', 'ttd_zimbra_preauth_admin_settings::page_init');
        add_action('admin_menu', 'ttd_zimbra_preauth_admin_settings::add_plugin_page' );

        $plugin = plugin_basename( ZIPA_PLUGIN_FILE );
        add_filter("plugin_action_links_$plugin", 'ttd_zimbra_preauth_admin_settings::settings_link' );
    }

    // Add settings link on plugin page
    public static function settings_link($links) { 
        $settings_link = '<a href="options-general.php?page=zimbra-preauth-settings">Settings</a>'; 
        array_unshift($links, $settings_link); 
        return $links; 
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
            'preauth_url', // ID
            'Pre-Auth URL', // Title 
            'ttd_zimbra_preauth_admin_settings::preauth_url_callback', // Callback
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

        add_settings_field(
            'link_text', // ID
            'Link Text', // Title 
            'ttd_zimbra_preauth_admin_settings::link_text_callback', // Callback
            self::PAGEID, // Page
            'zimbra_preauth_setting_section' // Section           
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

        if( isset( $input['preauth_key'] ) )
            $new_input['preauth_key'] = sanitize_text_field( $input['preauth_key'] );

        if( isset( $input['preauth_url'] ) )
            $new_input['preauth_url'] = sanitize_text_field( $input['preauth_url'] );

        if( isset( $input['login_url'] ) )
            $new_input['login_url'] = sanitize_text_field( $input['login_url'] );

        if( isset( $input['link_text'] ) )
            $new_input['link_text'] = htmlentities(stripslashes( $input['link_text'] ) );

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
    public static function preauth_url_callback()
    {
        printf(
            '<input class="regular-text code" type="text" id="preauth_url" name="zimbrapreauth[preauth_url]" value="%s" />',
            isset( self::$_options['preauth_url'] ) ? esc_attr( self::$_options['preauth_url'] ) : ''
        );
        print "<p class=\"description\">Enter Zimbras PreAuth Url (e.g https://zimbra.server.com/service/preauth)</p>";
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function login_url_callback()
    {
        printf(
            '<input class="regular-text code" type="text" id="login_url" name="zimbrapreauth[login_url]" value="%s" />',
            isset( self::$_options['login_url'] ) ? esc_attr( self::$_options['login_url'] ) : ''
        );
        print "<p class=\"description\">Enter Zimbras Url (e.g https://zimbra.server.com/)</p>";
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public static function link_text_callback()
    {
        print "<p class=\"description\">The text to be displayed as the html link to zimbra</p>";
        ?><p>
<textarea name="zimbrapreauth[link_text]" rows="10" cols="50" id="link_text" class="large-text code">
<?php print html_entity_decode( isset( self::$_options['link_text'] ) ? self::$_options['link_text'] : self::$_default['link_text'] ) ?>
</textarea>
</p><?php 
    }


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
 * Zimbra PreAuth Widget Class
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
        $options = get_option('zimbrapreauth');
        ?>
          <?php echo $before_widget; ?>
              <?php if ( !empty($title) )
                    echo $before_title . $title . $after_title; ?>
					
					<a href="/?pagename=zibra_pre_auth" target="_blank"><?php echo html_entity_decode($options['link_text']); ?></a>
                    
					
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
 
}
?>