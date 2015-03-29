<?php
//============================================================+
// File name   : tcpdf_config.php
// Begin       : 2004-06-11
// Last Update : 2009-09-30
//
// Description : Configuration file for TCPDF.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @copyright 2004-2008 Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com)
 *            Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @package com.tecnick.tcpdf
 * @version 4.0.014
 * @link http://tcpdf.sourceforge.net
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2004-10-27
 */

// If you define the constant K_TCPDF_EXTERNAL_CONFIG, the following settings will be ignored.
$kPathMain = KERNEL_LIBS_PATH . '/tcpdf/';
define('K_PATH_MAIN', $kPathMain);

/**
 * URL path to tcpdf installation folder (http://localhost/tcpdf/).
 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
 */
define('K_PATH_URL', '42');

/**
 * path for PDF fonts
 * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
 */
define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');

/**
 * cache directory for temporary files (full path)
 */
define('K_PATH_CACHE', K_PATH_MAIN . 'cache/');

/**
 * cache directory for temporary files (url path)
 */
define('K_PATH_URL_CACHE', K_PATH_URL . 'cache/');

/**
 *images directory
 */
define('K_PATH_IMAGES', K_PATH_MAIN . 'images/');

/**
 * blank image
 */
define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');

/**
 * page format
 */
define('PDF_PAGE_FORMAT', 'A4');

/**
 * page orientation (P=portrait, L=landscape)
 */
define('PDF_PAGE_ORIENTATION', 'P');

/**
 * document creator
 */
define('PDF_CREATOR', 'HTML2PDF - TCPDF');

/**
 * document author
 */
define('PDF_AUTHOR', 'HTML2PDF - TCPDF');

/**
 * header title
 */
define('PDF_HEADER_TITLE', null);

/**
 * header description string
 */
define('PDF_HEADER_STRING', null);

/**
 * image logo
 */
define('PDF_HEADER_LOGO', null);

/**
 * header logo image width [mm]
 */
define('PDF_HEADER_LOGO_WIDTH', null);

/**
 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
 */
define('PDF_UNIT', 'mm');

/**
 * header margin
 */
define('PDF_MARGIN_HEADER', 0);

/**
 * footer margin
 */
define('PDF_MARGIN_FOOTER', 0);

/**
 * top margin
 */
define('PDF_MARGIN_TOP', 0);

/**
 * bottom margin
 */
define('PDF_MARGIN_BOTTOM', 0);

/**
 * left margin
 */
define('PDF_MARGIN_LEFT', 0);

/**
 * right margin
 */
define('PDF_MARGIN_RIGHT', 0);

/**
 * default main font name
 */
define('PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * default main font size
 */
define('PDF_FONT_SIZE_MAIN', 10);

/**
 * default data font name
 */
define('PDF_FONT_NAME_DATA', 'helvetica');

/**
 * default data font size
 */
define('PDF_FONT_SIZE_DATA', 8);

/**
 * default monospaced font name
 */
define('PDF_FONT_MONOSPACED', 'courier');

/**
 * ratio used to adjust the conversion of pixels to user units
 */
define('PDF_IMAGE_SCALE_RATIO', 1);

/**
 * magnification factor for titles
 */
define('HEAD_MAGNIFICATION', 1);

/**
 * height of cell repect font height
 */
define('K_CELL_HEIGHT_RATIO', 1);

/**
 * title magnification respect main font size
 */
define('K_TITLE_MAGNIFICATION', 1);

/**
 * reduction factor for small font
 */
define('K_SMALL_RATIO', 2 / 3);

/**
 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
 */
define('K_THAI_TOPCHARS', true);

/**
 * if true allows to call TCPDF methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('K_TCPDF_CALLS_IN_HTML', false);