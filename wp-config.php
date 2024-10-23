<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_plugin_test' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Qz:T[Tgyy)1rUl`/6BUvs@S<]q1m@bgLr:@hg<)AHf@}GC)2#kZ58o#5pceUnjsq' );
define( 'SECURE_AUTH_KEY',  '1q!w<Vn}6#3wo6}*}(N$#<zvz:@wc?[o3Y*mA5HyADEPg:e;h`I0-LB8gy_FvS<V' );
define( 'LOGGED_IN_KEY',    'AYi-<7/ET5P`NR}1oe4&m>n@@]( fkn4vKUfF.u=gx?t9p8_:C/7E*ZqM5<G mkj' );
define( 'NONCE_KEY',        '13lXATj.c6)d`;PBOCdR,70q?wp6:F](VpiitzB/z^]2`ArU:U@Y~1l)IB)H|1IN' );
define( 'AUTH_SALT',        'd:L;3|3w.p;&8[MV2*`71ea:;=OA(oP,n?<::q0*FVLgbv1G]Yg7SF(Sm})L|ApR' );
define( 'SECURE_AUTH_SALT', ')xQ=[1,mNk[#}_?Rz}s?or%$E ?iBKCI;ixQaxUL?=b=i.( kO;EAgqIzdFenm,5' );
define( 'LOGGED_IN_SALT',   'ONfd[m:`X,Q 7~9,=vKr?tuLEJnbMKDZ1(CN9eIUQ=&`x?1xqRQ|m-y_{MU=!w2f' );
define( 'NONCE_SALT',       'I&RPKCAxNiJV@5l=bTG@+MW7-f`y18M*J4p~UudohyvGIiEeKdE)|nj8*bUs;;9q' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
