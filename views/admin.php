<?php

// Get a reference to the SysInfo instance
$dashdebug = DashDebug::get_instance();

// Now get information from the environment
global $wpdb, $apblue;
// Get MYSQL Version
$sqlversion = $wpdb->get_var( "SELECT VERSION() AS version" );
// GET SQL Mode
$mysqlinfo = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
if ( is_array( $mysqlinfo ) ) $sql_mode = $mysqlinfo[0]->Value;
if ( empty( $sql_mode ) ) $sql_mode = __( 'Not set', 'dashdebug' );
// Get PHP Safe Mode
if ( ini_get( 'safe_mode' ) ) $safe_mode = __( 'On', 'dashdebug' );
else $safe_mode = __( 'Off', 'dashdebug' );
// Get PHP allow_url_fopen
if ( ini_get( 'allow_url_fopen' ) ) $allow_url_fopen = __( 'On', 'dashdebug' );
else $allow_url_fopen = __( 'Off', 'dashdebug' );
// Get PHP Max Upload Size
if ( ini_get( 'upload_max_filesize' ) ) $upload_max = ini_get( 'upload_max_filesize' );
else $upload_max = __( 'N/A', 'dashdebug' );
// Get PHP Output buffer Size
if ( ini_get( 'pcre.backtrack_limit' ) ) $backtrack_limit = ini_get( 'pcre.backtrack_limit' );
else $backtrack_limit = __( 'N/A', 'dashdebug' );
// Get PHP Max Post Size
if ( ini_get( 'post_max_size' ) ) $post_max = ini_get( 'post_max_size' );
else $post_max = __( 'N/A', 'dashdebug' );
// Get PHP Max execution time
if ( ini_get( 'max_execution_time' ) ) $max_execute = ini_get( 'max_execution_time' );
else $max_execute = __( 'N/A', 'dashdebug' );
// Get PHP upload tmp directory
if ( ini_get( 'upload_tmp_dir' ) ) $upload_tmp = ini_get( 'upload_tmp_dir' );
else $upload_tmp = __( 'N/A', 'dashdebug' );
// Get PHP Include Path
if ( ini_get( 'include_path' ) ) $inc_path = ini_get( 'include_path' );
else $inc_path = __( 'N/A', 'dashdebug' );
// Get PHP Memory Limit
if ( ini_get( 'memory_limit' ) ) {$memory_limit = isset( $apblue->memory_limit ) ? $apblue->memory_limit : ini_get( 'memory_limit' );}
else $memory_limit = __( 'N/A', 'dashdebug' );
// Get actual memory_get_usage
if ( function_exists( 'memory_get_usage' ) ) $memory_usage = round( memory_get_usage() / 1024 / 1024, 2 );
else $memory_usage = __( 'N/A', 'dashdebug' );
// required for EXIF read
if ( is_callable( 'exif_read_data' ) ) $exif = __( 'Yes', 'dashdebug' ). " ( V" . substr( phpversion( 'exif' ), 0, 4 ) . ")" ;
else $exif = __( 'No', 'dashdebug' );
// required for meta data
if ( is_callable( 'iptcparse' ) ) $iptc = __( 'Yes', 'dashdebug' );
else $iptc = __( 'No', 'dashdebug' );
// required for meta data
if ( is_callable( 'xml_parser_create' ) ) $xml = __( 'Yes', 'dashdebug' );
else $xml = __( 'No', 'dashdebug' );
$theme = wp_get_theme();
$browser = $dashdebug->get_browser();
$plugins = $dashdebug->get_all_plugins();
$active_plugins = $dashdebug->get_active_plugins();
$all_options = $dashdebug->get_all_options();
$all_options_serialized = serialize( $all_options );
$all_options_bytes = round( mb_strlen( $all_options_serialized, '8bit' ) / 1024, 2 );
$all_options_transients = $dashdebug->get_transients_in_options( $all_options );
$debug_users = count_users();
?>

<div class="dashdebug">

		<div class="icon32">
			<img src="<?php echo DASHDEBUG_PLUGIN_URL ?>/img/dashdebug.png" />
		</div><!-- /.icon32 -->

		<h2 class="title"><?php _e( ' Dashboard Debug', 'dashdebug' ); ?></h2>

<section class="color-1">
	<p>
		<button class="btn btn-1 btn-1a">Button</button>
		<a href="#dd-themes"><button  class="btn btn-1 btn-1a">Theme</button></a>
		<a href="#dd-plugins"><button class="btn btn-1 btn-1a">Plugins</button></a>
	</p>
</section>

<?php
$data = array(
	'posts'       => wp_count_posts( 'post' )->publish,
	'pages'       => wp_count_posts( 'page' )->publish,
	'categories'  => wp_count_terms( 'category' ),
	'tags'        => wp_count_terms( 'post_tag' ),
	'comments'    => wp_count_comments()->approved,
	'comments_mod'    => wp_count_comments()->moderated,
	'comments_spam'    => wp_count_comments()->spam,
	'comments_trash'    => wp_count_comments()->trash,
	'attachments' => wp_count_posts( 'attachment' )->inherit
);
?>
<div id="dashboard-widgets" class="metabox-holder columns-2">

	<div id="postbox-container-1" class="postbox-container">
		<div id="normal-sortables" class="meta-box-sortables ui-sortable">
			<div id="" class="postbox ">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Right Now</span></h3>
				<div class="inside">
					<div class="table table_content">
						<p class="sub">Content</p>
						 <ul>
							<?php foreach ( $data as $label => $value ){
								echo "\t<li class=\"list-group-item\">" . number_format( $value ). " $label</li>\n";
							}?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="postbox-container-2" class="postbox-container">
		<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<div id="" class="postbox  hide-if-js" style="display: block;">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Debug Configuration Settings</span></h3>
				<div class="inside">
					<ul>
						<li>WP_DEBUG :          <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? _e( '<span class="db-enabled">Enabled</span>', 'dashdebug' ) . "\n" : _e( 'Disabled', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></li>
						<li>SCRIPT_DEBUG:       <?php echo defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG ? _e( 'Enabled', 'dashdebug' ) . "\n" : _e( '<span class="db-disabled">Disabled</span>', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></li>
						<li>SAVEQUERIES :       <?php echo defined( 'SAVEQUERIES' ) ? SAVEQUERIES ? _e( 'Enabled', 'dashdebug' ) . "\n" : _e( 'Disabled', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></li>
						<li>AUTOSAVE_INTERVAL : <?php echo defined( 'AUTOSAVE_INTERVAL' ) ? AUTOSAVE_INTERVAL ? AUTOSAVE_INTERVAL . "\n" : _e( 'Disabled', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></li>
						<li>WP_POST_REVISIONS : <?php echo defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS ? WP_POST_REVISIONS . "\n" : _e( 'Disabled', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="clearfix"></div>

<div class="panel panel-default">
  <div class="panel-heading">Pie Chart</div>
  <div class="panel-body">
    <div id="chart_div"></div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">Comment Visualization</div>
  <div class="panel-body">
    <div id="comment_chart_div"></div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">Debug Panel</div>
  <div class="panel-body"></div>

	<ul class="list-group">
	<li class="list-group-item"><?php _e( 'WordPress Version', 'dashdebug' ); ?>           : <span><?php echo get_bloginfo( 'version' ) . "\n"; ?></span></li>
	<li class="list-group-item"><?php _e( 'Operating System', 'dashdebug' ); ?>            : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo PHP_INT_SIZE * 8?>&nbsp;Bit)</span></li>
	<li class="list-group-item"><?php _e( 'Web Server', 'dashdebug' ); ?>                  : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Memory usage', 'dashdebug' ); ?>            : <span><?php echo $memory_usage . "M (" . round( $memory_usage / $memory_limit * 100, 0 ) . "%)\n"; ?></span>
	<div class="progress"><?php $memu = round( $memory_usage / $memory_limit * 100, 0 ); ?>
	  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $memu; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $memu; ?>%">
	    <span class="sr-only"><?php echo $memu; ?>%</span>
	  </div>
	</div>
	</li>
	<li class="list-group-item"><?php _e( 'MYSQL Version', 'dashdebug' ); ?>               : <span><?php echo $sqlversion; ?></span></li>
	<li class="list-group-item"><?php _e( 'SQL Mode', 'dashdebug' ); ?>                    : <span><?php echo $sql_mode; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Version', 'dashdebug' ); ?>                 : <span><?php echo PHP_VERSION; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Safe Mode', 'dashdebug' ); ?>               : <span><?php echo $safe_mode; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Allow URL fopen', 'dashdebug' ); ?>         : <span><?php echo $allow_url_fopen; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Memory Limit', 'dashdebug' ); ?>            : <span><?php echo $memory_limit; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Max Upload Size', 'dashdebug' ); ?>         : <span><?php echo $upload_max; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Max Post Size', 'dashdebug' ); ?>           : <span><?php echo $post_max; ?></span></li>
	<li class="list-group-item"><?php _e( 'PCRE Backtracking Limit', 'dashdebug' ); ?>     : <span><?php echo $backtrack_limit; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP Max Script Execute Time', 'dashdebug' ); ?> : <span><?php echo $max_execute; ?>s</span></li>
	<li class="list-group-item"><?php _e( 'PHP Upload tmp directory', 'dashdebug' ); ?>    : <span><?php echo $upload_tmp; ?>s</span></li>
	<li class="list-group-item"><?php _e( 'PHP Include path', 'dashdebug' ); ?>            : <span><?php echo $inc_path; ?>s</span></li>
	<li class="list-group-item"><?php _e( 'PHP Exif support', 'dashdebug' ); ?>            : <span><?php echo $exif; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP IPTC support', 'dashdebug' ); ?>            : <span><?php echo $iptc; ?></span></li>
	<li class="list-group-item"><?php _e( 'PHP XML support', 'dashdebug' ); ?>             : <span><?php echo $xml; ?></span></li>
	<li class="list-group-item"><?php _e( 'WordPress URL', 'dashdebug' ); ?>               : <span><?php echo get_bloginfo( 'wpurl' ) . "\n"; ?></span></li>
	<li class="list-group-item"><?php _e( 'Home URL: ', 'dashdebug' ); ?>                  : <span><?php echo get_bloginfo( 'url' ) . "\n"; ?></span></li>
	<li class="list-group-item"><?php _e( 'Users ', 'dashdebug' ); ?>						: <span><?php echo 'There are ', $debug_users['total_users'], ' total users'; ?></span></li>

	<li class="list-group-item"><?php _e( 'Content Directory', 'dashdebug' ); ?>      : <span><?php echo WP_CONTENT_DIR; ?></span></li>
	<li class="list-group-item"><?php _e( 'Content URL', 'dashdebug' ); ?>            : <span><?php echo WP_CONTENT_URL; ?></span></li>
	<li class="list-group-item"><?php _e( 'Plugins Directory:', 'dashdebug' ); ?>     : <span><?php echo WP_PLUGIN_DIR; ?></span></li>
	<li class="list-group-item"><?php _e( 'Plugins URL', 'dashdebug' ); ?>            : <span><?php echo WP_PLUGIN_URL; ?></span></li>
	<li class="list-group-item"><?php _e( 'Uploads Directory', 'dashdebug' ); ?>      : <span><?php echo ( defined( 'UPLOADS' ) ? UPLOADS : WP_CONTENT_DIR . '/uploads' ); ?></span></li>

	<li class="list-group-item"><?php _e( 'Cookie Domain', 'dashdebug' ); ?>          : <span><?php echo defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN ? COOKIE_DOMAIN . "\n" : _e( 'Disabled', 'dashdebug' ) . "\n" : _e( 'Not set', 'dashdebug' ) . "\n" ?></span></li>
	<li class="list-group-item"><?php _e( 'Multi-Site Active:', 'dashdebug' ); ?>     : <span><?php echo is_multisite() ? _e( 'Yes', 'dashdebug' ) . "\n" : _e( 'No', 'dashdebug' ) . "\n" ?></span></li>

	<li class="list-group-item"><?php _e( 'WP Options Count', 'dashdebug' ); ?>       : <span><?php echo count( $all_options ) . "\n"; ?></span></li>
	<li class="list-group-item"><?php _e( 'WP Options Size:', 'dashdebug' ); ?>       : <span><?php echo $all_options_bytes . "kb\n" ?></span></li>
	<li class="list-group-item"><?php _e( 'WP Options Transients', 'dashdebug' ); ?>  : <span><?php echo count( $all_options_transients ) . "\n"; ?></span></li>

	<li class="list-group-item"><?php _e( 'Operating System:', 'dashdebug' ); ?>    <?php echo $browser['platform']; ?></li>
	<li class="list-group-item"><?php _e( 'Browser:', 'dashdebug' ); ?>             <?php echo $browser['name'] . ' ' . $browser['version']; ?></li>
	<li class="list-group-item"><?php _e( 'User Agent:', 'dashdebug' ); ?>          <?php echo $browser['user_agent']; ?></li>

	</ul>
</div><!-- /.panel-default -->

<div class="panel panel-default">
	<div class="panel-heading"><?php _e( 'Active Theme', 'dashdebug' ); ?></div>
	<div class="panel-body"></div>
	<ul class="list-group">
		<li class="list-group-item"><?php echo $theme->get( 'Name' ); ?> <?php echo $theme->get( 'Version' ) . "\n"; ?></li>
		<li class="list-group-item"><?php echo '<a class="themeuri" href="' . $theme->get( 'ThemeURI' ) . '" >' . $theme->get( 'ThemeURI' ) . '</a>'; ?></li>
	</ul>
</div>

<div class="panel panel-default">
	<div class="panel-heading"><?php _e( 'Active Plugins', 'dashdebug' ); ?></div>
	<div class="panel-body">Active Plugins: <?php echo count($active_plugins); ?></div>
	<ul class="list-group">
		<?php
		foreach ( $plugins as $plugin_path => $plugin ) {

			// Only show active plugins
			if ( in_array( $plugin_path, $active_plugins ) ) {

				echo '<li class="list-group-item"> ' . $plugin['Name'] . ' ' . '<span class="plugversion">' . $plugin['Version'] . '</span>';

				if ( isset( $plugin['PluginURI'] ) ) {
					echo ' <a class="pluguri" href="' . $plugin['PluginURI'] . '" >' . $plugin['PluginURI'] . '</a>';
				} // end if

				echo "</li>";
			} // end if
		} // end foreach
		?>
	</ul>
</div><!-- /.panel-default -->
<?php
// $args=array(
//   'orderby' => 'name',
//   'order' => 'ASC'
//   );
// $categories=get_categories($args);
//   foreach($categories as $category) {
//     echo '<p>Category: <a href="' . get_category_link( $category->term_id ) . '" title="' . sprintf( __( "View all posts in %s" ), $category->name ) . '" ' . '>' . $category->name.'</a> </p> ';
//     echo '<p> Description:'. $category->description . '</p>';
//     echo '<p> Post Count: '. $category->count . '</p>';  }
?>
<div class="panel panel-default">
  <div class="panel-heading">Panel heading without title</div>
  <div class="panel-body">
    Panel content
  </div>
</div>




</div><!-- /#dashdebug -->
