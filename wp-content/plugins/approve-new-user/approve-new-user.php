<?php
/**
 * Plugin Name:       Approve New User
 * Description:       Approve New User plugin automates the user registration process on your WordPress website.
 * Version:           1.0.2
 * Author:            Geek Code Lab
 * Author URI:        https://geekcodelab.com/
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       approve-new-user
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'ANUIWP_VERSION', '1.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function anuiwp_activation_hook() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	ANUIWP_Activator::activate();
}

register_activation_hook( __FILE__, 'anuiwp_activation_hook' );

/**
 * The core plugin class that is used to define admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-approve-new-user.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
class ANUIWP_Approve_New_User
{
    /**
     * The only instance of ANUIWP_Approve_New_User.
     *
     * @var ANUIWP_Approve_New_User
     */
    private static $instance;

    /**
     * Flag to check if run has been executed.
     *
     * @var bool
     */
    private static $has_run = false;

    /**
     * Returns the main instance.
     *
     * @return ANUIWP_Approve_New_User
     */
    public static function instance()
    {
        $settings = array(
            'plugin_name'    => 'approve-new-user',
            'version'        => ANUIWP_VERSION,
            'get_plugin_url' => plugin_dir_url(__FILE__),
            'get_plugin_dir' => plugin_dir_path(__FILE__)
        );

        if (!isset(self::$instance)) {
            self::$instance = new ANUIWP_Approve_New_User_Main($settings);
            self::$instance->email_tags = new ANUIWP_Email_Template_Tags();
        }

        return self::$instance;
    }

    /**
     * Runs the main plugin functionality.
     */
    public function run()
    {
        if (!self::$has_run) {
            self::$instance->run();
            self::$has_run = true;
        }
    }
}

/**
 * Initialize the plugin and optionally run it.
 */
function anuiwp_approve_new_user($run = false)
{
    // Initialize and store the instance
    $plugin = ANUIWP_Approve_New_User::instance();

    // Optionally run the plugin
    if ($run) {
        $plugin->run();
    }

    return $plugin;
}

// Call this only if you want to initialize and run the plugin
anuiwp_approve_new_user(true);