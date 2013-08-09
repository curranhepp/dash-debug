<?php
/*
Plugin Name: Dash Debug Box
Plugin URI: http://example.com
Description: Display server information directly on the dashboard.
Version: 0.1
Author: Foo
Author URI: http://example.com
License: GPL2
*/

// Create the function use in the action hook
function serverinfo_add_dashboard_widgets() {
    wp_add_dashboard_widget('apblue_overview_server', 'Server Settings', 'apblue_overview_server');
}

// Hook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'serverinfo_add_dashboard_widgets' );

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
    // Get PHP Memory Limit
    if(ini_get('memory_limit')) {$memory_limit = isset($apblue->memory_limit) ? $apblue->memory_limit : ini_get('memory_limit');}
    else $memory_limit = __('N/A', 'apblue');
    // Get actual memory_get_usage
    if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte', 'apblue');
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
    <li><?php _e('Memory usage', 'apblue'); ?> : <span><?php echo $memory_usage; ?></span></li>
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
    <li><?php _e('PHP Exif support', 'apblue'); ?> : <span><?php echo $exif; ?></span></li>
    <li><?php _e('PHP IPTC support', 'apblue'); ?> : <span><?php echo $iptc; ?></span></li>
    <li><?php _e('PHP XML support', 'apblue'); ?> : <span><?php echo $xml; ?></span></li>
<?php
}