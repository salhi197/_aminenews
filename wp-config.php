<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u289619260_nAo9L' );

/** MySQL database username */
define( 'DB_USER', 'u289619260_8FW9Z' );

/** MySQL database password */
define( 'DB_PASSWORD', 'edG6SX6QCa' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          ';/A%`UZc.P|7S4JIQwZwO1[S]z/svM/AfR7Tk~oVwjpm|4*p5#qz+;9yJ.G,CLGm' );
define( 'SECURE_AUTH_KEY',   '2;{q(U21]L,NwFP95{&IiSVP#Kcx#IjkE|7~e0a~B9xUllo[5Wg8%=q/4FO/4m]|' );
define( 'LOGGED_IN_KEY',     '7Q&J.uTYyl#P&Eh>htQ<D0rV=gQL[#yH2)bA)@vjZ%}6Q//tGTbK7}!,GVkNa9CB' );
define( 'NONCE_KEY',         ' 9bu= 9BW4s(/u6~5Tk@$T+-hCIn`#nhyeY*6&Lg3Mjm-l=L@j@&v5V$U)RZ8m4N' );
define( 'AUTH_SALT',         'l%5^W]x5)3]UR)plH|LLP]Z(?9p9f+WWGLS4dQL*B&qrhqS0X)+)uuXT+(DXp}YM' );
define( 'SECURE_AUTH_SALT',  'Cs59,UP/FvYn*cy}Yf}t{UP~n$,er2N_]Z+ChdI+SCnc`jk]f/2i[F-d,*@t/r9t' );
define( 'LOGGED_IN_SALT',    '9T[%JGs.q%=;A4E*zA`JL.ebuqM1V@~4n@!d:CxWBwyz<gR$`@W^@Xq7E3S&_t9[' );
define( 'NONCE_SALT',        'H/(L[dklHdnjK8sGlOP1Y&Hry.w%[|001CQEQNU_0mR|u$g8;]BawH+dU:}b*W5p' );
define( 'WP_CACHE_KEY_SALT', '^jl,AwnG__H:FQ,FebLcBRHsv-(L1THUW!zH5|LI61V}EBM;Hqn1cPRjL_FW% uc' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
