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
define('DB_NAME', 'wordpress_staging');

/** MySQL database username */
define('DB_USER', 'staging_user');

/** MySQL database password */
define('DB_PASSWORD', '67U^{AA^cfh3K');

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
define('AUTH_KEY',         'U94Bh`5+ZvH2NTqs^m`&;|,zL>gg0}#zFV!`BN~>b/>I7|}AE|7%mV(ET*H-+D)w');
define('SECURE_AUTH_KEY',  '{ZFw*E5A+bJ_zl)9L=XzlgnU>d b`nGc6^C~tKedf8}2Gs-R-4+jauc-h18)%HTU');
define('LOGGED_IN_KEY',    '/^!v4^(86-y_-vm|3##C]]neHzo%xl9U{-.TS+Md!b==e1rW?K5_}#*=%R6B:,Ok');
define('NONCE_KEY',        'zIUsBayQL9y>>E:^wxeah_#q9VCA)lQ?f_`Qp|dE~4ARnj27(SNj6:v-.6[!2q|4');
define('AUTH_SALT',        '%^{DCKJRX5{qv^NW$lR?ELS5l+EXmBV205m0eeD7NEdU&^Wr_*dHVI/q7/-=C[]d');
define('SECURE_AUTH_SALT', 'e=nee2JpKZBX}W.]~UxRB<nG}HJ-#02=K-L;@x;{>Zcx&fRm[/Q28#&|Os/`e#sO');
define('LOGGED_IN_SALT',   'b^XuL$<|T>&.E?x=3:bD+L3;=g=[vo(;Y&MBYY8r?Z|5,&76qCsp-n.$n}NZ7aY:');
define('NONCE_SALT',       ' pFp}0PMIs2]Hs_Np{Bn|iF(gdjP,fH)B`;(*;[W1=[Ng)B::ZI-J<(B,]+q]Q<~');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
