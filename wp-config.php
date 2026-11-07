<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ag_flolist');

/** Database username */
define('DB_USER', 'ag_flolist_ys');

/** Database password */
define('DB_PASSWORD', '&Mzaau4!jRjFa7o9');

/** Database hostname */
define('DB_HOST', 'localhost:3306');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY',          'MV/DN|!evg`#dcr4Oy(-7CA7<@Ls4^dnJ?]3]d/tdzCMs9IQ;gja3YE5nT2*}}9y');
define('SECURE_AUTH_KEY',   'Tz+?fJzh}@yd,h{}jmPfl$E)JnMH#p}$[;<?NUHjadR)>k%nkjF$-^|T6iPg$xx>');
define('LOGGED_IN_KEY',     'zX6n;LP1lBIfx NXpv?}?.?=pJ0}V#l1@k}D?I76l@eMoMotS(+S:7)T~j`]J&e6');
define('NONCE_KEY',         'ji:}>.B?Yl75:rfGA *sO7kQ2R>0Soy}_=UUO|qg;1gc8P&:^n;(<&44nNPGfpk/');
define('AUTH_SALT',         'P47+#JjH;3ecDM5A=81YB^5@/;]07kUsH=P;SfL,t)iu6T*Lh7WXk7)7,~)VTT<a');
define('SECURE_AUTH_SALT',  'Aj+w5/V7ON:[ ,$02gt6B-,M)oT,SDXGAgB%}O4j?lkn,p;kdRu6iRK{362pxJJ~');
define('LOGGED_IN_SALT',    'e_o^9du97%q#oC2?zl6M0;1cSbGUfMC&8[G/5A_OZ<Y,ZT2Hx=C?*$/W.dn}g90P');
define('NONCE_SALT',        'z{%i2AUR+D8,.,PGw0m`:j@[9#n;Y1S<c.7LFh@ r(KK4D3*zO.1jf{e?!ZaHj0{');
define('WP_CACHE_KEY_SALT', '/2YjAX,=eQYw2J(8+o<1|RYo(-wz9guPe;|uvAzZw=~Z3U$#|i=%2F:d/Sk@8iu?');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'agf_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if (! defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

define('WP_ENVIRONMENT_TYPE', 'local');
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
