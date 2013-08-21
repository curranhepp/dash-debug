<?php



/**
 * Use this plugin to view system information about your WordPress install. Things like the versions of WordPress, PHP, and MySQL,
 * installed/activated plugins, the current theme, memory limit, allowable upload size, operating system, browser details, etc.
 * This information would be very useful to many users and for people that need to provide support for their plugins and themes.
 *
 * This plugin implements the singleton pattern so anytime you need access to an instance of the class, then call:
 * `$sysinfo = SysInfo::get_instance()`
 *
 * @version 0.2.0
 */
class DashDebug {

    /*--------------------------------------------------*
     * Attributes
     *--------------------------------------------------*/

    /** A reference to the instance of this class */
    private static $instance;

    /*--------------------------------------------------*
     * Constructor
     *--------------------------------------------------*/

    /**
     * The function used to set and/or retrieve the current instance of this class.
     *
     * @return  object  $instance   A reference to an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new self;
        } // end if

        return self::$instance;

    } // end get_instance;

    /**
     * The constructor for the class responsible for setting constant definitions, activation hooks,
     * and filters.
     */
    private function __construct() {

        // Global constants first
        define( 'DASHDEBUG_VERSION_KEY', 'dashdebug_version' );
        define( 'DASHDEBUG_VERSION_NUM', '0.2.0');
        define( 'DASHDEBUG_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
        define( 'DASHDEBUG_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . DASHDEBUG_PLUGIN_NAME );
        define( 'DASHDEBUG_PLUGIN_URL', WP_PLUGIN_URL . '/' . DASHDEBUG_PLUGIN_NAME );

        // Activation/deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'do_activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'do_deactivation' ) );

        // Any other hooks we need
        add_action( 'init', array($this, 'load_textdomain' ) );
        add_action( 'admin_notices', 'activate_issuem_admin_notice' );
        add_action( 'admin_menu', array($this, 'add_tools_page' ) );
        add_filter( 'plugin_action_links', array($this, 'add_action_links'), 10, 2 );

    } // end constructor

    /*--------------------------------------------------*
     * Hooks
     *--------------------------------------------------*/

    /**
     * Defines the textdomain for this plugin for localization and translation.
     */
    function load_textdomain() {
        load_plugin_textdomain('dashdebug', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    } // load_textdomain

    /**
     * Updates the version key and version number of the plugin in the options table.
     */
    function activate() {
        update_option( DASHDEBUG_VERSION_KEY, DASHDEBUG_VERSION_NUM );
    } // end activate

    /**
     * Deletes the version information from the database
     */
    function deactivate() {
        delete_option(DASHDEBUG_VERSION_KEY);
    } // end decativate

    /**
     * Fired on plugin activation.
     *
     * @param   boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    function do_activation( $network_wide ) {

        // If this plugin as called network wide, then call the functionality for each site
        if( $network_wide ) {

            $this->call_function_for_each_site( array( $this, 'activate' ) );

        // Otherwise, simply call it for this single site
        } else {

            $this->activate();

        } // end if/else

    } // do_activation

    /**
     * Fired on plugin deactivation.
     *
     * @param   boolean $network_wide   True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    function do_deactivation( $network_wide ) {

        // If this plugin as called network wide, then call the functionality for each site
        if( $network_wide ) {

            $this->call_function_for_each_site( array( $this, 'deactivate' ) );

        // Otherwise, simply call it for this single site
        } else {

            $this->deactivate();

        } // end if/else

    } // end do_deactivation

    /**
     * The callback function used if this plugin is being used in a multisite environment. It's responsible for iterating
     * through each of the blogs, then gahtering the information for each blog.
     *
     * @param   function    $function   The callback function to fire for each blog.
     */
    function call_function_for_each_site( $function ) {

        global $wpdb;

        // Hold this so we can switch back to it
        $current_blog = $wpdb->blogid;

        // Get all the blogs/sites in the network and invoke the function for each one
        $blog_ids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
        foreach( $blog_ids as $blog_id ) {

            switch_to_blog( $blog_id );
            call_user_func( $function );

        } // end foreach

        // Now switch back to the root blog
        switch_to_blog( $current_blog );

    } // end call_function_for_each_site

    /**
     * Adds the 'dashdebug' page to the 'Tools' menu.
     */
    function add_tools_page() {

        $admin_pages = array();

        // Build the options for the submenu page API call
        $parent_slug = 'tools.php';
        $page_title = __( 'Dashboard Debug', 'dashdebug' );
        $sub_menu_title = __( 'DashDebug', 'dashdebug' );
        $capability = 'manage_options';
        $menu_slug = 'dashdebug';
        $function = array( $this, 'add_dashdebug_page' );

        // Actually add the page as a subemnu
        $admin_pages[] = add_submenu_page( $parent_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function );

        // Include the stylesheet for the given page
        foreach( $admin_pages as $admin_page) {
            add_action( "admin_print_styles-{$admin_page}", array( $this, 'add_admin_styles' ) );
        } // end foreach

    } // end add_tools_page

    /**
     * Includes the admin view for the dashdebug page.
     */
    function add_dashdebug_page() {
        require_once 'views/admin.php';
    } // end add_dashdebug_page

    /**
     * Adds the stylesheet for the dashdebug admin page.
     */
    function add_admin_styles() {
        wp_enqueue_style( 'dashdebug-css', DASHDEBUG_PLUGIN_URL . '/css/dashdebug.css', DASHDEBUG_VERSION_NUM );
    } // end add_admin_styles

    /**
     * Creates the action links for this plugin including the links and filename.
     *
     * @param   array   $links  Links to the edit and activate features of this plugin.
     * @param   string  $file   The actual plugin file of this plugin.
     * @return  array   $links  Includes links to Activate, Edit, and Delete
     */
    function add_action_links( $links, $file ) {

        static $this_plugin;

        if ( ! $this_plugin ) {
            $this_plugin = plugin_basename(__FILE__);
        } // end if

        if( $file == $this_plugin ) {

            $packs_link = '<a href="' . admin_url( 'tools.php?page=dashdebug' ) . '">' . __( 'View', 'dashdebug' ) . '</a>';
            array_unshift( $links, $packs_link );

        } // end if

        return $links;

    } // end add_action_links

    /**
     * Helper function used print error nag if IssueM is not activated
     *
     * @since 1.0.0
     */
    function activate_issuem_admin_notice() { ?>

        <div class="updated fade">
            <p><?php _e( "Error! You must have the IssueM plugin activated to use IssueM's Magazine Theme.", 'issuem-magazine' ); ?></p>
        </div>

        <?php
    }

    /**
     * Determines which browser is currently being used to view this installation of WordPress.
     *
     * @return  array   Includes information on user_agent, name, version, platform, and pattern.
     */
    function get_browser() {

        // http://www.php.net/manual/en/function.get-browser.php#101125.
        // Cleaned up a bit, but overall it's the same.

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser_name = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        // First get the platform
        if (preg_match('/linux/i', $user_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $user_agent)) {
            $platform = 'Windows';
        } // end if/else

        // Next get the name of the user agent yes seperately and for good reason
        if( preg_match( '/MSIE/i', $user_agent ) &&  ! preg_match( '/Opera/i', $user_agent ) ) {

            $browser_name = 'Internet Explorer';
            $browser_name_short = "MSIE";

        } elseif( preg_match( '/Firefox/i', $user_agent ) ) {

            $browser_name = 'Mozilla Firefox';
            $browser_name_short = "Firefox";

        } elseif( preg_match( '/Chrome/i', $user_agent ) ) {

            $browser_name = 'Google Chrome';
            $browser_name_short = "Chrome";

        } elseif( preg_match( '/Safari/i', $user_agent ) ) {

            $browser_name = 'Apple Safari';
            $browser_name_short = "Safari";

        } elseif( preg_match( '/Opera/i', $user_agent ) ) {

            $browser_name = 'Opera';
            $browser_name_short = "Opera";

        } elseif( preg_match('/Netscape/i', $user_agent ) ) {

            $browser_name = 'Netscape';
            $browser_name_short = "Netscape";

        } // end if/else

        // Finally get the correct version number
        $known = array( 'Version', $browser_name_short, 'other' );
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if( ! preg_match_all( $pattern, $user_agent, $matches ) ) {
            // We have no matching number just continue
        } // end if

        // See how many we have
        $i = count( $matches['browser'] );
        if( $i != 1 ) {

            // We will have two since we are not using 'other' argument yet
            // See if version is before or after the name
            if (strripos($user_agent, "Version") < strripos($user_agent, $browser_name_short)){
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            } // end if/else

        } else {
            $version= $matches['version'][0];
        } // end if/else

        // Check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        } // end if/else

        return array(
            'user_agent'    => $user_agent,
            'name'          => $browser_name,
            'version'       => $version,
            'platform'      => $platform,
            'pattern'       => $pattern
        );

    } // end get_browser

    /**
     * From the Codex:
     *
     *  "Check the plugins directory and retrieve all plugin files with plugin data."
     *
     * @return  array   The array of plugins currently installed in the environment.
     */
    function get_all_plugins() {
        return get_plugins();
    } // end get_all_plugins

    /**
     * Retrieves all a list of the active plugins.
     *
     * @return  array   The list of active plugins.
     */
    function get_active_plugins() {
        return get_option( 'active_plugins', array() );
    } // end get_active_plugins

    /**
     * Retrieves the amount of memory being used by the installation along with the themes, plugins, etc.
     *
     * @return  float   The amount of memory being used by this installation.
     */
    function get_memory_usage() {
        return round( memory_get_usage() / 1024 / 1024, 2 );
    } // end get_memory_usage

    /**
     * From the Codex:
     *
     *  "Retrieve all autoload options or all options, if no autoloaded ones exist."
     *
     * @return  array   $options    All of the options that exist in the WordPress installation
     */
    function get_all_options() {

        // Not to be confused with the core deprecated get_alloptions
        return wp_load_alloptions();

    } // end get_all_options

    /**
     * Gathers a list of all of the transients set in the plugin.
     *
     * @param   array   $options        The array of options managed by the installation of WordPress
     * @return  array   $transients     The array of transients currently stored by WordPress
     */
    function get_transients_in_options( $options ) {

        $transients = array();

        foreach( $options as $name => $value ) {

            if( stristr( $name, 'transient' ) ) {
                $transients[ $name ] = $value;
            } // end if

        } // end foreach

        return $transients;

    } // end get_transients_in_options

} // end class

// Let's get this party started
DashDebug::get_instance();

/**
 * Creates a custom post type for case-studies
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 * @link http://codex.wordpress.org/Glossary#Post_Type
 */
function create_post_type() {
    $labels = array(
        'name'               => __( 'Tutorials', 'apblue' ), // general name for the post type, usually plural. The same as, and overridden by $post_type_object->label
        'singular_name'      => __( 'Tutorial', 'apblue' ), // name for one object of this post type. Defaults to value of name
        'menu_name'          => __( 'Tutorials', 'apblue' ), // the menu name text. This string is the name to give menu items. Defaults to value of name
        'all_items'          => __( 'All Tutorials', 'apblue' ), // the all items text used in the menu. Default is the Name label
        'add_new'            => __( 'Add New Tutorial', 'apblue' ), // the add new text. The default is Add New for both hierarchical and non-hierarchical types. When internationalizing this string, please use a gettext context matching your post type. Example: _x('Add New', 'product');
        'add_new_item'       => __( 'Add New Tutorial', 'apblue' ), // the add new item text. Default is Add New Post/Add New Page
        'edit_item'          => __( 'Edit Tutorial', 'apblue' ), // the edit item text. Default is Edit Post/Edit Page
        'new_item'           => __( 'New Tutorial', 'apblue' ), // the new item text. Default is New Post/New Page
        'view_item'          => __( 'View Tutorial', 'apblue' ), // the view item text. Default is View Post/View Page
        'search_items'       => __( 'Search Tutorials', 'apblue' ), // the search items text. Default is Search Posts/Search Pages
        'not_found'          => __( 'No Tutorials found', 'apblue' ), // the not found text. Default is No posts found/No pages found
        'not_found_in_trash' => __( 'No Tutorials found in Trash', 'apblue' ), // the not found in trash text. Default is No posts found in Trash/No pages found in Trash
        'parent_item_colon'  => __( 'Parent Tutorial', 'apblue' ) // the parent text. This string isn't used on non-hierarchical types. In hierarchical ones the default is Parent Page
    );
    $args = array(
        'public'              => true, // Whether post type is intended to be used publicly either via the admin interface or by front-end users.
        'exclude_from_search' => true, // exclude posts with this post type from front end search results.
        'has_archive'         => false,
        'label'               => 'Tutorials', // A plural descriptive name for the post type marked for translation.
        'name'                => __( 'tutorials' ), // general name for the post type, usually plural. The same as, and overridden by $post_type_object->label
        'labels'              => $labels, // An array of labels for this post type. By default post labels are used for non-hierarchical types and page labels for hierarchical ones.
        'singular_name'       => __( 'tutorials' ), // name for one object of this post type. Defaults to value of name
        'menu_position'       => 99, // 5 = below Posts: The position in the menu order the post type should appear. show_in_menu must be true. Default: null - defaults to below Comments https://codex.wordpress.org/Function_Reference/add_menu_page#Parameters
        'menu_icon'           => plugins_url( 'img/custom-post-icon.png' , __FILE__ ), /* the icon for the custom post type menu */
        'supports'            => array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail', 'post-formats' ) // An alias for calling add_post_type_support() directly. As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.
    );
    register_post_type( 'tutorial', $args );
}
// add_action( 'init', 'create_post_type' );


// Create the function use in the action hook
function serverinfo_add_dashboard_widgets() {
    wp_add_dashboard_widget('apblue_overview_server', 'Server Settings', 'apblue_overview_server');
}

// Hook into the 'wp_dashboard_setup' action to register our other functions
// add_action('wp_dashboard_setup', 'serverinfo_add_dashboard_widgets' );

/**
 * Show the server settings in a dashboard widget
 *
 * @return void
 */
function apblue_overview_server() {
?>
<div id="dashboard_server_settings" class="dashboard-widget-holder wp_dashboard_empty">
    <div class="apblue-dashboard-widget">
        <div class="dashboard-widget-content">
            <ul class="settings">
                <?php apblue_get_serverinfo(); ?>
            </ul>
            <p><strong><?php _e('Graphic Library', 'apblue'); ?></strong></p>
            <ul class="settings">
                <?php apblue_gd_info(); ?>
            </ul>
        </div>
    </div>
</div>
<?php
}


/**
 * Show GD Library version information
 *
 * @return void
 */
function apblue_gd_info() {

    if(function_exists("gd_info")){
        $info = gd_info();
        $keys = array_keys($info);
        for($i=0; $i<count($keys); $i++) {
            if(is_bool($info[$keys[$i]]))
                echo "<li> " . $keys[$i] ." : <span>" . apblue_gd_yesNo($info[$keys[$i]]) . "</span></li>\n";
            else
                echo "<li> " . $keys[$i] ." : <span>" . $info[$keys[$i]] . "</span></li>\n";
        }
    }
    else {
        echo '<h4>'.__('No GD support', 'apblue').'!</h4>';
    }
}


/**
 * Return localized Yes or no
 *
 * @param bool $bool
 * @return return 'Yes' | 'No'
 */
function apblue_gd_yesNo( $bool ){
    if($bool)
        return __('Yes', 'apblue');
    else
        return __('No', 'apblue');
}


/**
 * Show some server info
 * @author GamerZ (http://www.lesterchan.net)
 *
 * @return void
 */
function apblue_get_serverinfo() {

    global $wpdb, $apblue;
    // Get MYSQL Version
    $sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
    // GET SQL Mode
    $mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
    if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
    if (empty($sql_mode)) $sql_mode = __('Not set', 'apblue');
    // Get PHP Safe Mode
    if(ini_get('safe_mode')) $safe_mode = __('On', 'apblue');
    else $safe_mode = __('Off', 'apblue');
    // Get PHP allow_url_fopen
    if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On', 'apblue');
    else $allow_url_fopen = __('Off', 'apblue');
    // Get PHP Max Upload Size
    if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
    else $upload_max = __('N/A', 'apblue');
    // Get PHP Output buffer Size
    if(ini_get('pcre.backtrack_limit')) $backtrack_limit = ini_get('pcre.backtrack_limit');
    else $backtrack_limit = __('N/A', 'apblue');
    // Get PHP Max Post Size
    if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
    else $post_max = __('N/A', 'apblue');
    // Get PHP Max execution time
    if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
    else $max_execute = __('N/A', 'apblue');
    // Get PHP upload tmp directory
    if(ini_get('upload_tmp_dir')) $upload_tmp = ini_get('upload_tmp_dir');
    else $upload_tmp = __('N/A', 'apblue');
    // Get PHP Include Path
    if(ini_get('include_path')) $inc_path = ini_get('include_path');
    else $inc_path = __('N/A', 'apblue');
    // Get PHP Memory Limit
    if(ini_get('memory_limit')) {$memory_limit = isset($apblue->memory_limit) ? $apblue->memory_limit : ini_get('memory_limit');}
    else $memory_limit = __('N/A', 'apblue');
    // Get actual memory_get_usage
    if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2);
    else $memory_usage = __('N/A', 'apblue');
    // required for EXIF read
    if (is_callable('exif_read_data')) $exif = __('Yes', 'apblue'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
    else $exif = __('No', 'apblue');
    // required for meta data
    if (is_callable('iptcparse')) $iptc = __('Yes', 'apblue');
    else $iptc = __('No', 'apblue');
    // required for meta data
    if (is_callable('xml_parser_create')) $xml = __('Yes', 'apblue');
    else $xml = __('No', 'apblue'); ?>

    <li><?php _e('Operating System', 'apblue'); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo (PHP_INT_SIZE * 8) ?>&nbsp;Bit)</span></li>
    <li><?php _e('Server', 'apblue'); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
    <li><?php _e('Memory usage', 'apblue'); ?> : <span><?php echo $memory_usage . "M (" . round($memory_usage / $memory_limit * 100, 0) . "%)\n"; ?></span></li>
    <li><?php _e('MYSQL Version', 'apblue'); ?> : <span><?php echo $sqlversion; ?></span></li>
    <li><?php _e('SQL Mode', 'apblue'); ?> : <span><?php echo $sql_mode; ?></span></li>
    <li><?php _e('PHP Version', 'apblue'); ?> : <span><?php echo PHP_VERSION; ?></span></li>
    <li><?php _e('PHP Safe Mode', 'apblue'); ?> : <span><?php echo $safe_mode; ?></span></li>
    <li><?php _e('PHP Allow URL fopen', 'apblue'); ?> : <span><?php echo $allow_url_fopen; ?></span></li>
    <li><?php _e('PHP Memory Limit', 'apblue'); ?> : <span><?php echo $memory_limit; ?></span></li>
    <li><?php _e('PHP Max Upload Size', 'apblue'); ?> : <span><?php echo $upload_max; ?></span></li>
    <li><?php _e('PHP Max Post Size', 'apblue'); ?> : <span><?php echo $post_max; ?></span></li>
    <li><?php _e('PCRE Backtracking Limit', 'apblue'); ?> : <span><?php echo $backtrack_limit; ?></span></li>
    <li><?php _e('PHP Max Script Execute Time', 'apblue'); ?> : <span><?php echo $max_execute; ?>s</span></li>
    <li><?php _e('PHP Upload tmp directory', 'apblue'); ?> : <span><?php echo $upload_tmp; ?>s</span></li>
    <li><?php _e('PHP Include path', 'apblue'); ?> : <span><?php echo $inc_path; ?>s</span></li>
    <li><?php _e('PHP Exif support', 'apblue'); ?> : <span><?php echo $exif; ?></span></li>
    <li><?php _e('PHP IPTC support', 'apblue'); ?> : <span><?php echo $iptc; ?></span></li>
    <li><?php _e('PHP XML support', 'apblue'); ?> : <span><?php echo $xml; ?></span></li>
<?php
}
/**
 * This is the title and content that you want to appear in the admin pointer. Do not change
 * the HTML tags, only modify the text within the single quotes.
 */
function get_server_helper_content() {
    $pointer_content  = '<h3>' . __( 'Server information is displayed here.', 'apblue' ) . '</h3>';
    $pointer_content .= '<p>' . __( 'There are currently no options.', 'apblue' ) . '</p>'; ?>

    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($) {
            $('#dashboard_server_settings').pointer({
                pointerWidth: 340,
                content: '<?php echo $pointer_content; ?>',
                position: {
                    my: 'left top',
                    at: 'center top',
                    offset: '-25 0'
                },
                close: function() {
                    setUserSetting( 'appdash', '1' );
                }
            }).pointer('open');
        });
        //]]>
    </script>

<?php
}

function get_tutorial_nav_helper_content() {
    $pointer_content  = '<h3>' . __( 'A new post type!.', 'apblue' ) . '</h3>';
    $pointer_content .= '<p>' . __( 'Tutorials will show you more of how Wordpress works and let you play around in a safe environment.', 'apblue' ) . '</p>'; ?>

    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($) {
            $('#menu-posts-tutorial').pointer({
                pointerWidth: 340,
                content: '<?php echo $pointer_content; ?>',
                position: {
                    my: 'left top',
                    at: 'center',
                    offset: '-25 0'
                },
                close: function() {
                    setUserSetting( 'apptutnav', '1' );
                }
            }).pointer('open');
        });
        //]]>
    </script>

<?php
}

function ab2_enqueue_wp_pointer( $hook_suffix ) {
    $enqueue = FALSE;

    $admin_bar = get_user_setting( 'apptutnav', 0 ); // check settings on user
    // check if admin bar is active and default filter for wp pointer is true
    if ( ! $admin_bar && apply_filters( 'show_wp_pointer_admin_bar', TRUE ) ) {
        $enqueue = TRUE;
        add_action( 'admin_print_footer_scripts', 'get_tutorial_nav_helper_content' );
    }
    // in true, include the scripts
    if ( $enqueue ) {
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script( 'utils' ); // for user settings
    }
}
// add_action( 'admin_enqueue_scripts', 'ab2_enqueue_wp_pointer' );

function ab_enqueue_wp_pointer( $hook_suffix ) {
    $enqueue = FALSE;

    $admin_bar = get_user_setting( 'appdash', 0 ); // check settings on user
    // check if admin bar is active and default filter for wp pointer is true
    if ( ! $admin_bar && apply_filters( 'show_wp_pointer_admin_bar', TRUE ) ) {
        $enqueue = TRUE;
        add_action( 'admin_print_footer_scripts', 'get_server_helper_content' );
    }
    // in true, include the scripts
    if ( $enqueue ) {
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script( 'utils' ); // for user settings
    }
}
add_action( 'admin_enqueue_scripts', 'ab_enqueue_wp_pointer' );