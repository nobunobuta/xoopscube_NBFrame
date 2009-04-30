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
$GLOBALS['_NBFrame_ShortURL_ExtraFrontEnd'] = true;
$GLOBALS['_NBFrame_ShortURL_ExtraFrontEnd'] = array();
if (file_exists(XOOPS_ROOT_PATH.'/settings/ShortURL.inc.php')) {
    include XOOPS_ROOT_PATH.'/settings/ShortURL.inc.php';
}

class ShortURL extends XCube_ActionFilter
{
    var $mMultiViews = true;
    var $mExtraFrontEnd = array();

    function ShortURL() {
        if (isset($GLOBALS['_NBFrame_ShortURL_MultiViews'])) {
            $this->mMultiViews = $GLOBALS['_NBFrame_ShortURL_MultiViews'];
        }
        if (isset($GLOBALS['_NBFrame_ShortURL_ExtraFrontEnd'])) {
            $this->mExtraFrontEnd = $GLOBALS['_NBFrame_ShortURL_ExtraFrontEnd'];
        }
    }

    function preBlockFilter()
    {
       ob_start(array(&$this, 'convertShortURL'));
    }
    
    function convertShortURL($str)
    {
        $moduleDirArray = glob(XOOPS_ROOT_PATH.'/modules/*', GLOB_ONLYDIR);
        $fromURLPatterns = array();
        $toURLStrings = array();
        if (is_array($this->mExtraFrontEnd)) {
            foreach($this->mExtraFrontEnd as $frontend) {
                $frontend = basename($frontend);
                if (file_exists(XOOPS_ROOT_PATH.'/'.$frontend.'.php')) {
                    $sigFunc = 'NBFrameShortURL_sig_'.$frontend;
                    if (!function_exists($sigFunc)) {
                        include XOOPS_ROOT_PATH.'/'.$frontend.'.php';
                    }
                    if (function_exists($sigFunc)) {
                        $dirName = $sigFunc();
                        $this->getReplacePattern($fromURLPatterns, $toURLStrings, $dirName, $frontend);
                    }
                }
            }
        }
        foreach ($moduleDirArray as $moduleDir) {
            $dirName = basename($moduleDir);
            if ($dirName == 'user') continue;
            if (file_exists(XOOPS_ROOT_PATH.'/'.$dirName.'.php')) {
                $sigFunc = 'NBFrameShortURL_sig_'.$dirName;
                if (!function_exists($sigFunc)) {
                    include XOOPS_ROOT_PATH.'/'.$dirName.'.php';
                }
                if (function_exists($sigFunc) && ($sigFunc() == $dirName)) {
                    $this->getReplacePattern($fromURLPatterns, $toURLStrings, $dirName, $dirName);
                }
            }
        }
        $result = preg_replace($fromURLPatterns, $toURLStrings, $str);
        return $result;
    }
    
    function getReplacePattern(&$from, &$to, $dirName, $frontend) {
        if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/include/NBFrameLoader.inc.php')) {
            $from[] = '/'.preg_quote(XOOPS_URL.'/modules/'.$dirName.'/','/').'(page\/)?/';
            $from[] = '/'.preg_quote(rawurlencode(XOOPS_URL.'/modules/'.$dirName.'/'),'/').'('.preg_quote(rawurlencode('page/'),'/').')?/';
        } else {
            $from[] = '/'.preg_quote(XOOPS_URL.'/modules/'.$dirName.'/','/').'/';
            $from[] = '/'.preg_quote(rawurlencode(XOOPS_URL.'/modules/'.$dirName.'/'),'/').'/';
        }
        if ($this->mMultiViews) {
            $to[] = XOOPS_URL.'/'.$frontend.'/';
            $to[] = rawurlencode(XOOPS_URL.'/'.$frontend.'/');
        } else {
            $to[] = XOOPS_URL.'/'.$frontend.'.php/';
            $to[] = rawurlencode(XOOPS_URL.'/'.$frontend.'/');
        }
    }
}
