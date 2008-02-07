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
class ShortURL extends XCube_ActionFilter
{
    var $mMultiViews = true;
    function preBlockFilter()
    {
       ob_start(array(&$this, 'convertShortURL'));
    }
    
    function convertShortURL($str)
    {
        $moduleDirArray = glob(XOOPS_ROOT_PATH.'/modules/*', GLOB_ONLYDIR);
        $fromURLPatterns = array();
        $toURLStrings = array();
        foreach ($moduleDirArray as $moduleDir) {
            $dirName = basename($moduleDir);
            if ($dirName == 'user') continue;
            if (file_exists(XOOPS_ROOT_PATH.'/'.$dirName.'.php')) {
                if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/include/NBFrameLoader.inc.php')) {
                    $fromURLPatterns[] = '/'.preg_quote(XOOPS_URL.'/modules/'.$dirName.'/','/').'(page\/)?/';
                    $fromURLPatterns[] = '/'.preg_quote(rawurlencode(XOOPS_URL.'/modules/'.$dirName.'/'),'/').'('.preg_quote(rawurlencode('page/'),'/').')?/';
                } else {
                    $fromURLPatterns[] = '/'.preg_quote(XOOPS_URL.'/modules/'.$dirName.'/','/').'/';
                    $fromURLPatterns[] = '/'.preg_quote(rawurlencode(XOOPS_URL.'/modules/'.$dirName.'/'),'/').'/';
                }
                if ($this->mMultiViews) {
                    $toURLStrings[] = XOOPS_URL.'/'.$dirName.'/';
                    $toURLStrings[] = rawurlencode(XOOPS_URL.'/'.$dirName.'/');
                } else {
                    $toURLStrings[] = XOOPS_URL.'/'.$dirName.'.php/';
                    $toURLStrings[] = rawurlencode(XOOPS_URL.'/'.$dirName.'/');
                }
            }
        }
        $result = preg_replace($fromURLPatterns, $toURLStrings, $str);
        return $result;
    }
}
