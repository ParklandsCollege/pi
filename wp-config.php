<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pi');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'XW4IU0dFg+-=+pXmm{j}tR9I7@8[}-y+QX`tM2,~}EQCVw22}ZL%o&8tX3j{-XGf');
define('SECURE_AUTH_KEY',  '1<<YQ]sT~)4I_-V6aO6PuM)6HR6 N.$;B{@fW-^}*TVl+ Nh,V|eg6natfS`*IA.');
define('LOGGED_IN_KEY',    '+s<`QxB^*f-B2v-2r)/pwLe1lg=lqw;LE&^%iVq`K-i?3ert0w9U)CSi&)0]iucG');
define('NONCE_KEY',        'G?>GH?m,mwp_e|Net|9,=X9|b5 }I GP#$)6A*[.J7|Q-+=]Gaa{}}4@$zwC },,');
define('AUTH_SALT',        'Ta@V+NJ0E{@*+,U*tE>KQ2kSKB&X51hnTgRm[:eG#Kx|{Ho2k+)-t;I:KCqFBDT?');
define('SECURE_AUTH_SALT', 'AS1!5,UuV9RG?Q[{L~71}gmU+-]MAXce>5sn!M%c*v=sJ~jW3>B)e,p8!-|rdyH+');
define('LOGGED_IN_SALT',   'I^$3DrdSva?guC7QH7Hrt~n_;iO|Y]-]d:AXn`O8><A##1ITO%A,(-$-oizxX!$C');
define('NONCE_SALT',       ':p]PqHJo_]1n-aJ6G8SY3t@v@%A8T^2&=U7Q^`!804q@c=/`=mXXh=BO?)XVNppT');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'localhost');
define('PATH_CURRENT_SITE', '/parklands/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
