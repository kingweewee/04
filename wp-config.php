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
//define('DB_NAME', 'terryr_flamepizza');

/** MySQL database username */
//define('DB_USER', 'terryr_flamewp');

/** MySQL database password */
//define('DB_PASSWORD', 'ww2580');

/** The name of the database for WordPress */
define('DB_NAME', 'jamesh_onestepDB');

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

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'L %+Vr=8VN7gAb$.}7C94d~}>6F]zi@.+x| |0~jl,vm#t}??lDKo3CBNnZaL)9j');
define('SECURE_AUTH_KEY',  'JTC!oWK DR12TM(2x$NTPb:<>p@KaLV8#j.`Y>T(5hP?IoEyoiy3)W?LeX*-2=1;');
define('LOGGED_IN_KEY',    'B(2Cx-:OJ F>3xSdcDb2I?OUFotuEHbX1^ RhFW(j.7%[=]ZQNe*<+U[JVs9!n-O');
define('NONCE_KEY',        'Z*ShQhY78ai$;U D3Io2M`E)<Iz(/-&njJ(&_X(|/OrLjC|C1/y*JKk0|9|G>vBE');
define('AUTH_SALT',        'Q+4yJUG}5,Zb<v$n.rcCKlpd*_tB@fSBtm?CBKmRKH6#B4pgh*VW`f|Lo2X8^mm(');
define('SECURE_AUTH_SALT', '5z+xvKa8*~4Q&aHP&vO11fs6PbZm7=x^a}7 vv5#Shf}ygjU-4e`xy?eU$U,h/D-');
define('LOGGED_IN_SALT',   '>hnk$i`+,emq^4|`M1sHW-F>7#XwAfe`3_veeu-^XSfo^O)-b?<qBxJ,k+`F?/s-');
define('NONCE_SALT',       '_NAH.B#3@*<|2>T,*&C$2?EH288}.RZ-MH>Xw/`E3[ptU8;zBT(v^9#:)<s+]2&-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'os_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
