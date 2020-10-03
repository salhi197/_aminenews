<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ays-pro.com/
 * @since             1.0.0
 * @package           Secure_Copy_Content_Protection
 *
 * @wordpress-plugin
 * Plugin Name:       Secure Copy Content Protection
 * Plugin URI:        https://ays-pro.com/index.php/wordpress/secure-copy-content-protection/
 * Description:       Copy Protection plugin is activated it disables the right click, copy paste, content selection and copy shortcut keys
 * Version:           2.2.0
 * Author:            Copy Content Protection Team
 * Author URI:        https://ays-pro.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       secure-copy-content-protection
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SCCP_NAME_VERSION', '2.2.0');
define('SCCP_NAME', 'secure-copy-content-protection');
if (!defined('SCCP_ADMIN_URL')) {
	define('SCCP_ADMIN_URL', plugin_dir_url(__FILE__) . 'admin');
}

if (!defined('SCCP_PUBLIC_URL')) {
	define('SCCP_PUBLIC_URL', plugin_dir_url(__FILE__) . 'public');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-secure-copy-content-protection-activator.php
 */
function activate_secure_copy_content_protection() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-secure-copy-content-protection-activator.php';
	Secure_Copy_Content_Protection_Activator::activate();
}

/**
 * The code that runs after plugin activation.
 * This action is documented in includes/class-simple-google-maps-activator.php
 */
function ays_sccp_activation_redirect_method( $plugin ) {
	if ($plugin == plugin_basename(__FILE__)) {
		exit(wp_redirect(admin_url('admin.php?page=secure-copy-content-protection')));
	}
}

add_action('activated_plugin', 'ays_sccp_activation_redirect_method');
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-secure-copy-content-protection-deactivator.php
 */
function deactivate_secure_copy_content_protection() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-secure-copy-content-protection-deactivator.php';
	Secure_Copy_Content_Protection_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_secure_copy_content_protection');
register_deactivation_hook(__FILE__, 'deactivate_secure_copy_content_protection');
add_action('plugins_loaded', 'activate_secure_copy_content_protection');
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-secure-copy-content-protection.php';

/**
 * Defining DB content
 */
require plugin_dir_path(__FILE__) . 'db.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function sccp_admin_notice() {
	if (isset($_GET['page']) && strpos($_GET['page'], SCCP_NAME) !== false) {
		?>
        <div class="ays-notice-banner container-fluid">
            <div class="navigation-bar">
                <div id="navigation-container">
                    <a class="logo-container" href="http://ays-pro.com/" target="_blank">
                        <img class="logo" src="<?php echo SCCP_ADMIN_URL . '/images/ays_pro.png'; ?>" alt="AYS Pro logo"
                             title="AYS Pro logo"/>
                    </a>
                    <ul id="menu">                        
                        <li><a class="ays-btn" href="https://ays-pro.com/index.php/wordpress/secure-copy-content-protection" target="_blank">PRO</a></li>
                        <li><a class="ays-btn" href="https://ays-pro.com/wordpress-copy-content-protection-user-manual" target="_blank">Documentation</a></li>
                        <li><a class="ays-btn" href="https://wordpress.org/support/plugin/secure-copy-content-protection/reviews/" target="_blank">Rate Us</a></li>
                        <li><a class="ays-btn" href="https://freedemo.ays-pro.com/copy-protection-demo-free/" target="_blank">Demo</a></li>
                        <li><a class="ays-btn" href="https://wordpress.org/support/plugin/secure-copy-content-protection/" target="_blank">Support Forum</a></li>
                        <li><a class="ays-btn" href="http://ays-pro.com/index.php/contact/" target="_blank">Contact us</a></li>
                    </ul>
                </div>
            </div>
        </div>
		<?php
	}
}

function run_secure_copy_content_protection() {
	add_action('admin_notices', 'sccp_admin_notice');
	$plugin = new Secure_Copy_Content_Protection();
	$plugin->run();

}

run_secure_copy_content_protection();
