<?php
// ** MySQL settings - You can get this info from your web host ** //
define('DB_NAME', 'runo');
define('DB_USER', 'dbmasteruser');
define('DB_PASSWORD', '1kv0^`hmR+cluh5BXGmUbQr:94gJF?ME');
define('DB_HOST', 'ls-2cbf013e36d9da5c471e289f9069efd752a5931a.cfm66se44mq6.ap-south-1.rds.amazonaws.com:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');



/**#@+
 * Authentication Unique Keys and Salts.
 * You can generate these at: https://api.wordpress.org/secret-key/1.1/salt/
 */
define('AUTH_KEY',         'AZQ1/SpZIkj%q^:w]!r$jw2w[&jUX{8<%[^ka)RcFJ:+xr3A;#zdd7}z5/Fd!>?!');
define('SECURE_AUTH_KEY',  '}sFPv*<#bz%68`A-zBxYPQ-CJjK9:Qenl2f)Hw5~+{FSbf/evaW*`;G6J3bPnu#$');
define('LOGGED_IN_KEY',    'Z@bm)4q%yo.+4-i8TU&=CUzxSV}HF)ioS% z[jl){`&Tq?3uCm^f[GRUo1>j2Zkc');
define('NONCE_KEY',        '8e<yrF-m(TQam4Ekz^C.rpGV6X6`:ED5yp5A~D%BD <w=SY4=ZcK.6:OBVBu7m?1');
define('AUTH_SALT',        'D(p?LX-D4nBjh^Zpsb9;d5I7@enVY?bDoljRs55x[$=r%*lRc.aY%.@=)_gAx-M+');
define('SECURE_AUTH_SALT', 'k2DlD1#)5-R]>tpDQPMesW:QM%dTs<3[mU cm]LJ]gN8NOuLd|=H<}1rV*[&6s,I');
define('LOGGED_IN_SALT',   '0UXn8xMq(,!aoCF|I;?C4#}6(Ftad2RWv&bDP_!<Ktd}F:i1 .i-=hLnTN=_{2O6');
define('NONCE_SALT',       'o2^(aKi`m~x#`BxS#LCLsMEmNjn[|=`46soaX>eQO06zFCY7OJ~vY*N~7LO23bIH');

/**#@-*/

/**
 * WordPress Database Table prefix.
 * You can have multiple installations in one database if you give each a unique prefix.
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 * Change this to true to enable the display of notices during development.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
