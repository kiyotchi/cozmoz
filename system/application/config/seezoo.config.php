<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ==========================================================
 * Seezoo system constants
 *
 * @package Seezoo Core
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * ==========================================================
 */

/**
 * ========================================================
 * Seezoo version
 * current system version
 * ========================================================
 */

define('SEEZOO_VERSION', '1.2.0');


/**
 * ========================================================
 * Your Server timezone
 * Seezoo uses date functions that depend on date.timezone settings.
 * So, please set timezone on your country.
 * If value is empty, we use server settings.
 * ========================================================
 */

define('SEEZOO_TIMEZONE', 'Asia/Tokyo');


/**
 * ========================================================
 * Page title format on each page
 * output <title> value format.
 * Fisrt %s is page title.
 * Second %s is your site title.
 * We output page title on this format with sprintf().
 * ========================================================
 */

define('SEEZOO_TITLE_FORMAT', '%s :: %s');


/**
 * ========================================================
 * Login segment string
 * Change the login URI segment for security, If you want.
 * ========================================================
 */

define('SEEZOO_SYSTEM_LOGIN_URI', 'login');


/**
 * ========================================================
 * Feature phone string encoding
 * Some feature phone cannot treat UTF-8 encoding...
 * So, output encoding can changes that you can.
 * Default value is Shift_JIS.
 * ========================================================
 */

define('SEEZOO_MOBILE_STRING_ENCODING', 'Shift_JIS');


/**
 * ========================================================
 * Need to convert SHIFT_JIS on Feature phone carrier strings
 * If carrier name matched in string,
 * we convert to SHIFT_JIS at output,
 * and convert to UTF-8 at input variables.
 * always lowercase
 * 
 * Known carrier:
 * Docomo   : need to convert SHIFT_JIS
 * AU       : supported UTF-8
 * Softbank : supported UTF-8
 * Willcom  : supported UTF-8
 * ========================================================
 */

define('SEEZOO_CONVERT_MOBILE_CARRIERS', 'docomo');


/**
 * ========================================================
 * PHP internal encoding on your server
 * If Your server can't change server encoding,
 * set your server encoding.
 * Seezoo auto convert input data to UTF-8.
 * please set uppercase format like these:
 * 
 * SHIFT_JIS
 * EUC-JP
 * UTF-8
 * ========================================================
 */

define('SEEZOO_SERVER_ENCODING', 'UTF-8');


/**
 * ========================================================
 * Move Origin Sitepath
 * Need Slash of last segment!
 * ========================================================
 */

define('MOVE_ORIGIN_SITE', 'your-origin-site/');


/**
 * ========================================================
 * Routing Priority
 * Site routing poricy.
 * 
 * First, routing by CI Controllers on system pages.
 * Second, routing by CMS pages.
 * Finaly, routing by Static pages on statics/ of page path.
 * 
 * 
 * If your site has static page more than CMS pages, 
 * Priority should be set 'static'(move from origin site recently),
 * else, your site has CMS page more than static pages, Priority shuld be set 'cms'.
 * 
 * Please set Appropriate mode to routing speed up!
 * ========================================================
 */

define('ROUTING_PRIORITY', 'cms');


/**
 * ========================================================
 * Original extensions directory ( for override )
 * ========================================================
 */

define ('SZ_EXT_DIR', 'sz_packages/');


/**
 * ========================================================
 * Plugin package directory
 * ========================================================
 */

define('SZ_PLG_DIR', 'sz_plugins/');


/**
 * ========================================================
 * Password encrypt algorithm
 * system password stretching encrypt algorithm.
 * md5, sha1(default), sha256...
 * ========================================================
 */

define('SEEZOO_PASSWORD_ENCRYPT_ALGORITHM', 'sha1');


/**
 * ========================================================
 * Password stretching times
 * ========================================================
 */

define('SEEZOO_PASSWORD_STRETCH_TIMES', 1000);



/**
 * ========================================================
 * Build extension and plugin path
 * ========================================================
 */

if (defined('FCPATH'))
{
	define('SZ_EXT_PATH', FCPATH . SZ_EXT_DIR);
	define('SZ_PLG_PATH', FCPATH . SZ_PLG_DIR);
}
else
{
	define('SZ_EXT_PATH', SZ_EXT_DIR);
	define('SZ_PLG_PATH', SZ_PLG_DIR);
}

// builtin template for seezoo-more
define('SEEZOO_DEFAULT_INSTALL_PACKAGE', 'hospital');
