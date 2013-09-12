<?php
/**
 * DashDebug.
 *
 * @package   DashDebug
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

class DashDebug {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var   string
	 */
	protected $version = '0.3.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'dashdebug';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Global constants first
		define( 'DASHDEBUG_VERSION_KEY', 'dashdebug_version' );
		define( 'DASHDEBUG_VERSION_NUM', '0.2.0' );
		define( 'DASHDEBUG_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
		define( 'DASHDEBUG_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . DASHDEBUG_PLUGIN_NAME );
		define( 'DASHDEBUG_PLUGIN_URL', WP_PLUGIN_URL . '/' . DASHDEBUG_PLUGIN_NAME );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		add_action( 'TODO', array( $this, 'action_method_name' ) );
		add_filter( 'TODO', array( $this, 'filter_method_name' ) );

		// add_action( 'admin_notices', array( $this, 'activate_dashdebug_admin_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'category_chart_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'comment_chart_data' ) );
		add_action( 'admin_menu', array( $this, 'add_tools_page' ) );
		// Add an action link pointing to the options page. TODO: Rename "plugin-name.php" to the name your plugin
		// $plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'plugin-name.php' );
		// add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Adds the stylesheet for the dashdebug admin page.
	 */
	function add_admin_styles() {
		wp_enqueue_style( 'dashdebug-css', DASHDEBUG_PLUGIN_URL . '/css/admin.css', DASHDEBUG_VERSION_NUM );
	} // end add_admin_styles

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		// if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
		//  return;
		// }

		$screen = get_current_screen();
		// if ( $screen->id == $this->plugin_screen_hook_suffix ) {
		wp_enqueue_script( $this->plugin_slug . '-admin-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		// wp_enqueue_script( $this->plugin_slug . '-admin-script-boot', plugins_url( 'js/bootstrap3.0.0rc2.min.js', __FILE__ ), array( 'jquery' ), $this->version );
		// }

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'plugin-name' to the name of your plugin
		 */
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

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
		$function = array( $this, 'display_plugin_admin_page' );

		// Actually add the page as a subemnu
		$admin_pages[] = add_submenu_page( $parent_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function );

		// Include the stylesheet for the given page
		foreach ( $admin_pages as $admin_page ) {
			add_action( "admin_print_styles-{$admin_page}", array( $this, 'add_admin_styles' ) );
			add_action( "admin_enqueue_scripts-{$admin_page}", array( $this, 'enqueue_admin_scripts' ) );
			// add_action( "admin_enqueue_scripts-{$admin_page}", array( $this, 'category_chart_data' ) );
		} // end foreach

	} // end add_tools_page

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once 'views/admin.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'plugins.php?page=plugin-name' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter hook callback here
	}

	/**
	 * Display notice on activate
	 *
	 * @since 1.0.0
	 */
	function activate_dashdebug_admin_notice() { ?>

        <div class="updated fade">
            <p><?php _e( "This is a notice.", 'dashdebug' ); ?></p>
        </div>

        <?php
	}

	function category_chart_data() {
		$args=array(
			'orderby' => 'name',
			'order' => 'ASC'
		);
		//Labels your chart, this represent the Column title and percentage.
		/*
        note that one column is in "string" format and another one is in "number" format
        as pie chart only required "numbers" for calculating percentage
        And string will be used for Slice title
    */

		$rows = array();
		$table = array();


		//Labels your chart, this represent the Column title and percentage.
		/*
        note that one column is in "string" format and another one is in "number" format
        as pie chart only required "numbers" for calculating percentage
        And string will be used for Slice title
    */
		$table['cols'] = array(
			array( 'label' => 'Category', 'type' => 'string' ),
			array( 'label' => 'Percentage', 'type' => 'number' )
		);
		$categories=get_categories( $args );
		foreach ( $categories as $category ) {
			// $temp[] = array('c' => $category->name);
			// $temp[] = array('v' => (string)(int)$category->count);
			// echo $foobar;
			// $temp[] = array('v' => (string) $category['name']);
			// $temp[] = array('v' => (int) $category['count']);
			// $rows[] = array('c' => (int) $category->count);
			$temp = array();

			// the following line will used to slice the Pie chart
			$temp[] = array( 'v' => (string) $category->name );

			//Values of the each slice
			$temp[] = array( 'v' => (int) $category->count );
			$rows[] = array( 'c' => $temp );
		}
		$table['rows'] = $rows;
		// convert data into JSON format
		$jsonTable = json_encode( $table );

		// wp_enqueue_script('gooapi', 'https://www.google.com/jsapi',array( 'jquery' ));?>
          <script type="text/javascript" src="https://www.google.com/jsapi"></script>

       <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.load('visualization', '1', {'packages':['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);

        function drawChart() {

          // Create our data table out of JSON data loaded from server.
          var data = new google.visualization.DataTable(<?php echo $jsonTable?>);
          var options = {
               title: 'Categories',
              is3D: 'false',
              width: 500,
              height: 300
            };
          // Instantiate and draw our chart, passing in some options.
          //do not forget to check ur div ID
          var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
          chart.draw(data, options);
        }
        </script>
        <?php

	}

	function comment_chart_data() {
		$table['cols'] = array(
			array( 'label' => 'Comment Status', 'type' => 'string' ),
			array( 'label' => 'Percentage', 'type' => 'number' )
		);
		$data = array(
			'Approved'    => wp_count_comments()->approved,
			'Moderated'    => wp_count_comments()->moderated,
			'Spam'    => wp_count_comments()->spam,
			'Trash'    => wp_count_comments()->trash
		);

		foreach ( $data as $label => $value ) {

			$temp = array();

			// the following line will used to slice the Pie chart
			$temp[] = array( 'v' => (string) $label );

			//Values of the each slice
			$temp[] = array( 'v' => (int) ( $value ) );
			$rows[] = array( 'c' => $temp );
		}

		$table['rows'] = $rows;
		// convert data into JSON format
		$jsonTable = json_encode( $table ); ?>

        <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.load('visualization', '1', {'packages':['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);

        function drawChart() {

          // Create our data table out of JSON data loaded from server.
          var data = new google.visualization.DataTable(<?php echo $jsonTable?>);
          var options = {
               title: 'Comments',
              is3D: 'false',
              width: 500,
              height: 300
            };
          // Instantiate and draw our chart, passing in some options.
          //do not forget to check ur div ID
          var chart = new google.visualization.PieChart(document.getElementById('comment_chart_div'));
          chart.draw(data, options);
        }
        </script>
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
		if ( preg_match( '/linux/i', $user_agent ) ) {
			$platform = 'Linux';
		} elseif ( preg_match( '/macintosh|mac os x/i', $user_agent ) ) {
			$platform = 'Mac';
		} elseif ( preg_match( '/windows|win32/i', $user_agent ) ) {
			$platform = 'Windows';
		} // end if/else

		// Next get the name of the user agent yes seperately and for good reason
		if ( preg_match( '/MSIE/i', $user_agent ) &&  ! preg_match( '/Opera/i', $user_agent ) ) {

			$browser_name = 'Internet Explorer';
			$browser_name_short = "MSIE";

		} elseif ( preg_match( '/Firefox/i', $user_agent ) ) {

			$browser_name = 'Mozilla Firefox';
			$browser_name_short = "Firefox";

		} elseif ( preg_match( '/Chrome/i', $user_agent ) ) {

			$browser_name = 'Google Chrome';
			$browser_name_short = "Chrome";

		} elseif ( preg_match( '/Safari/i', $user_agent ) ) {

			$browser_name = 'Apple Safari';
			$browser_name_short = "Safari";

		} elseif ( preg_match( '/Opera/i', $user_agent ) ) {

			$browser_name = 'Opera';
			$browser_name_short = "Opera";

		} elseif ( preg_match( '/Netscape/i', $user_agent ) ) {

			$browser_name = 'Netscape';
			$browser_name_short = "Netscape";

		} // end if/else

		// Finally get the correct version number
		$known = array( 'Version', $browser_name_short, 'other' );
		$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if ( ! preg_match_all( $pattern, $user_agent, $matches ) ) {
			// We have no matching number just continue
		} // end if

		// See how many we have
		$i = count( $matches['browser'] );
		if ( $i != 1 ) {

			// We will have two since we are not using 'other' argument yet
			// See if version is before or after the name
			if ( strripos( $user_agent, "Version" ) < strripos( $user_agent, $browser_name_short ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			} // end if/else

		} else {
			$version= $matches['version'][0];
		} // end if/else

		// Check if we have a number
		if ( $version == null || $version == "" ) {
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

		// Retrieve all autoload options or all options, if no autoloaded ones exist. This function caches its results.
		return wp_load_alloptions();

	} // end get_all_options

	/**
	 * Gathers a list of all of the transients set in the plugin.
	 *
	 * @param array   $options The array of options managed by the installation of WordPress
	 * @return  array   $transients     The array of transients currently stored by WordPress
	 */
	function get_transients_in_options( $options ) {

		$transients = array();

		foreach ( $options as $name => $value ) {

			if ( stristr( $name, 'transient' ) ) {
				$transients[ $name ] = $value;
			} // end if

		} // end foreach

		return $transients;

	} // end get_transients_in_options

	/**
	 * Get the current IP address.
	 *
	 * @param boolean $safe
	 * @return string IP address
	 * 2011-11-02 ms
	 */
	function getClientIp( $safe = true ) {
		if ( !$safe && env( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddr = preg_replace( '/(?:,.*)/', '', env( 'HTTP_X_FORWARDED_FOR' ) );
		} else {
			if ( env( 'HTTP_CLIENT_IP' ) ) {
				$ipaddr = env( 'HTTP_CLIENT_IP' );
			} else {
				$ipaddr = env( 'REMOTE_ADDR' );
			}
		}

		if ( env( 'HTTP_CLIENTADDRESS' ) ) {
			$tmpipaddr = env( 'HTTP_CLIENTADDRESS' );

			if ( !empty( $tmpipaddr ) ) {
				$ipaddr = preg_replace( '/(?:,.*)/', '', $tmpipaddr );
			}
		}
		return trim( $ipaddr );
	}

}
