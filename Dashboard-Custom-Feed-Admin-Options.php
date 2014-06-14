<?php
class DCFA_SETTINGS
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'DCF Admin Settings', 
            'manage_options', 
            'dfca-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'dcfa-option', array('total_items' => 3, 'feed_name' => 'DFC Feed') );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Dashboard Custom Feed</h2>
            <div class="">
                <p>This plugin was created by <a href="http://enshrined.co.uk" target="_NEW">Daryll Doyle</a> Senior Developer at <a href="http://www.digitalwebmedia.co.uk" target="_NEW">Digital Web Media Limited</a></p>
            </div>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'dcfa-settings' );   
                do_settings_sections( 'dfca-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'dcfa-settings', // Option group
            'dcfa-option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Dashboard Custom Feed Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'dfca-setting-admin' // Page
        );  

        add_settings_field(
            'total_items', // ID
            'Number of items to show:', // Title 
            array( $this, 'total_items_callback' ), // Callback
            'dfca-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'feed_name', 
            'Feed Name', 
            array( $this, 'feed_name_callback' ), 
            'dfca-setting-admin', 
            'setting_section_id'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['total_items'] ) )
            $new_input['total_items'] = absint( $input['total_items'] );

        if( isset( $input['feed_name'] ) )
            $new_input['feed_name'] = sanitize_text_field( $input['feed_name'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function total_items_callback()
    {
        printf(
            '<input type="text" id="total_items" name="dcfa-option[total_items]" value="%s" />',
            isset( $this->options['total_items'] ) ? esc_attr( $this->options['total_items']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function feed_name_callback()
    {
        printf(
            '<input type="text" id="feed_name" name="dcfa-option[feed_name]" value="%s" />',
            isset( $this->options['feed_name'] ) ? esc_attr( $this->options['feed_name']) : ''
        );
    }
}

if( is_admin() )
    $my_settings_page = new DCFA_SETTINGS();