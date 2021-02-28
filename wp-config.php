<?php

define('REVISR_GIT_PATH', 'https://github.com/marclpez/FLYGRAM.git'); // Added by Revisr
define('FS_METHOD', 'direct');

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
define( 'DB_NAME', 'dbs1514059' );

/** MySQL database username */
define( 'DB_USER', 'dbu971343' );

/** MySQL database password */
define( 'DB_PASSWORD', 'zKaCOqCyHkivjtMzfvlo' );

/** MySQL hostname */
define( 'DB_HOST', 'db5001841349.hosting-data.io' );

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
define( 'AUTH_KEY',          'BZ<X S?0:4[:Fnbf$&9enm(b$^pzKp :f=|tR7{%J+G7|^xpRh[N,G~K9l:o5@ik' );
define( 'SECURE_AUTH_KEY',   'ETWW:~evqs(Y3_v!:m5h4Hs8SPDgMj_/6lRgPqx& I6zXBO2F-&SHu5B|Rcmx5Fv' );
define( 'LOGGED_IN_KEY',     '!@-B]kj(B lFs3FcboRPK:e]-0`S94[|u.z,aP>IP>faGx(>>W1WB0IDhn$Q?[|J' );
define( 'NONCE_KEY',         '=aG TaY1uU|S;7!Bn/Ki~jR8tzVdrN!c-.4[WWVhmfw!:=^`X]YyW*r%DJ;g%nr*' );
define( 'AUTH_SALT',         '=_$F?K./1h-X!ez+^$mA)o5%M{]^,%O1Z1}1fg6WA*Rhhy8W:u?;3xwXWY_Y!E,:' );
define( 'SECURE_AUTH_SALT',  'k{Qv/(@NqMj/h#4 >!d(I__5*|r+ }!sUj$0?MQQ$WF<jFz:cdm&^&M8(4P3;-/9' );
define( 'LOGGED_IN_SALT',    ' ^^I`XMV{8<6Yp($!U!7t,<Jc`+F38D0lR]Ww+>4A #]]&UX^KX|+xACP#~x^]gb' );
define( 'NONCE_SALT',        ']Whm>~W._iA-H+nx;573}D:+ o;L_6rd}Snu,&ba:V;/L]]p,KzL.RWvF@|_<dhB' );
define( 'WP_CACHE_KEY_SALT', '.f`+_*bm]_,>,KcN^2as]ZRcgH XdRV?$s+uti1FvvSnMGf :rIV=Bf~zy!MLlmY' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'VuRPxcyK';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
