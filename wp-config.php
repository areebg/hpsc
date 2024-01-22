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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'hpsc' );

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
define( 'AUTH_KEY',         'ogs]*(|Q* R//UPB&~5NV;Xk_iY4KZ350ft^bJ)+>:N-)0pfKj62|JbNI=?SoC9:' );
define( 'SECURE_AUTH_KEY',  '$p|#-kJ}P9e#|GDWQdA.X@7y+:PB[G~rO{E,?j.m+1epqByOyg8u(hn5T2wL9yvM' );
define( 'LOGGED_IN_KEY',    'pCd9cHDl7)3H9hq]i`L86,q/5)oj%hwQc;Y^h}/O6w>.%5`L22t?Sx0T0YPI^AfC' );
define( 'NONCE_KEY',        'Fuqt]sq%uAO6@k}_o<odVDURT=>)4dh;c1C_9/h2!n-Ivl<#ZvOw2G]_7L|MucZO' );
define( 'AUTH_SALT',        'T%V`/*trdrntN|}X->qJ)+QYir_Zvsck[#aXB?00.:*0?@>hE|*UejSKC?BYDsC~' );
define( 'SECURE_AUTH_SALT', 'th A3|SL)8uw>/*$Hqnwle=[YgeW.,<6$i(KgWisVoVf@[A.i;[q?&IR$pna+Sjl' );
define( 'LOGGED_IN_SALT',   ']]%FEM{I56_2nY<Oul|L9JhM5Vwz3b,2uCk?;gyt)^&0hhXF.TvU>9#q~xMLdqe4' );
define( 'NONCE_SALT',       '~^6BNY tO5-.I2K~j&X#rsj-Zb;WnPY^~Kh50vFLy)E hf_7jaL&*+C8! nt@M%<' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
