<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if(!defined('NBFRAME_COMMON_FUNCTION_INCLUDED')){
    define('NBFRAME_COMMON_FUNCTION_INCLUDED', 1) ;

    if (preg_match('/^4/',PHP_VERSION)) {
        include_once (dirname(__FILE__).'/NBFramePHP4.inc.php');
    } else {
        include_once (dirname(__FILE__).'/NBFramePHP5.inc.php');
    }
}
?>
