<?php

/**
 * File: Shared Configurations for WordPress
 *
 * Some WordPress configurations are the same whatever environment
 * you're using, like the authentication salts. Common configuration
 * should be found in this file.
 *
 * Table of Contents :
 *    • Get Boilerplate Settings
 *    • Database Settings
 *    • Authentication Keys & Salts
 *
 * @see wp-config.php, local-config-sample.php
 * @link http://codex.wordpress.org/Roles_and_Capabilities
 */

/* ==========================================================================
   Boilerplate Settings
   ========================================================================== */

if ( ! defined('ASSET_VERSION') ) define( 'ASSET_VERSION', '56cf6f85c23d8' );

/* ==========================================================================
   Database Settings
   ========================================================================== */

/**
 * Give each a unique prefix for multiple installations in one database.
 * Only numbers, letters, and underscores please!
 */

if ( ! isset( $table_prefix ) ) $table_prefix = ( getenv('DB_PREFIX') ?: 'boilerplate_' );


/* ==========================================================================
   Authentication Keys & Salts
   ========================================================================== */

/**
 * @link https://api.wordpress.org/secret-key/1.1/salt/
 * @since 2.6.0
 */

define('AUTH_KEY',         ':[CI:@{v<@e bD8+jay+}F>?x,p?[),msqf+I1$a*+uZPs<bnOE!=j{PH/m&F_7u');
define('SECURE_AUTH_KEY',  '%1oK&,V 3|TUS&c?dC]@lHW`Ci0Rq?k)5w2iv5+s;yB|c@8><X0I/O`BBp/aw_@^');
define('LOGGED_IN_KEY',    '}u:*m;FYA{-e|`4q]LpuUc71;(qklI<]J&AITG3n.I!q(] U9px[Eqpe6J~E gl ');
define('NONCE_KEY',        'fz?,>Osz,73{%t<r-*hs{7y{:GOC*|+F{&<{AFUEes88Z3eQQ^mL@Bos;,&G*3Jz');
define('AUTH_SALT',        'eP+-t_)Kq3J{h|!i{>QyYna0Zpqo0=VePCsu#Z,&2ya#g[7-:&Tqe{W^CyK/JpU[');
define('SECURE_AUTH_SALT', '(--ip>+D&$0|<n]^*;I=eA-z(Lbg-Mgg6GiJBwRHFF2X=>)~{I<-*{+m@2OR(B#w');
define('LOGGED_IN_SALT',   '{x=|~Nli9fFq2&o{Eu3)?+!r{I$~$]xAn|(]xq2f_A1$|M/8Sl||8^WC-(B bda&');
define('NONCE_SALT',       'VzNND$2YRcFmH$T2`<,l`wv$.]z4FNg@rqB)La6GoOdJ|]mx$mVL0S,f+>&Z<!<+');