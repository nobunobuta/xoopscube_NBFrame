<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formtextarea.php";
NBFrame::using('Language');
class NBFrameFormHtmlTextArea extends XoopsFormTextArea
{
    var $mWidth;
    var $mHeight;
    var $mUseHtmlEditor;
    var $mCanUseHtmlEditor;
    var $mCaptionOrig;
    var $mGetPrefix = 'NBFormEditUse';

    /**
     * Constructor
     *
     * @param   string  $caption    Caption
     * @param   string  $name       "name" attribute
     * @param   string  $value      Initial text
     * @param   str     $rows       width param
     * @param   str     $cols       height param
     */
    function NBFrameFormHtmlTextArea($caption, $name, $value='', $rows=25, $cols=80, $height='500px', $width='100%')
    {
        $this->XoopsFormTextArea($caption, $name, $value, $rows, $cols);
        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mUseHtmlEditor = false;
        $this->mCanUseHtmlEditor=true;
        $this->mCaptionOrig = $caption;
        $this->switchEditorByGetParam();
    }

    function setCaption($caption) {
        $this->mCaptionOrig = $caption;
        $nullLanguageManager =& new NBFrameLanguage(NBFrame::null());
        $url = NBFrame::getCurrentURL();
        $url = NBFrame::removeQueryArgs($url, array($this->mGetPrefix.'_html',$this->mGetPrefix.'_xcode'));
        if ($this->mUseHtmlEditor) {
            $url = NBFrame::addQueryArgs($url, array($this->mGetPrefix.'_xcode'=>'1'));
            $desc = '<br /><small>&nbsp;<a href="'.$url.'">'.$nullLanguageManager->__l('Switching to XCode Editor').'</a></small>';
        } else {
            if ($this->mCanUseHtmlEditor) {
                $url = NBFrame::addQueryArgs($url, array($this->mGetPrefix.'_html'=>'1'));
                $desc = '<br /><small>&nbsp;<a href="'.$url.'">'.$nullLanguageManager->__l('Switching to HTML Editor').'</a></small>';
            } else {
                $desc = '';
            }
        }
        parent::setCaption($this->mCaptionOrig.$desc);
    }

    function setSwitchGetParamPrefix($prefix='form_use') {
        $this->mGetPrefix = $prefix;
    }

    function switchEditorByGetParam() {
        if (isset($_GET[$this->mGetPrefix.'_html']) && $_GET[$this->mGetPrefix.'_html']==1) {
            $this->useHtmlEditor(true);
        } else if (isset($_GET[$this->mGetPrefix.'_xcode']) && $_GET[$this->mGetPrefix.'_xcode']==1) {
            $this->useHtmlEditor(false);
        }
    }

    function useHtmlEditor($switch=true) {
        if ($this->mCanUseHtmlEditor) {
            $this->mUseHtmlEditor = $switch;
        } else {
            $this->mUseHtmlEditor = false;
        }
        $this->setCaption($this->mCaptionOrig);
    }


    function enableHtmlEditor() {
        $this->mCanUseHtmlEditor = true;
        $this->setCaption($this->mCaptionOrig);
    }

    function disableHtmlEditor() {
        $this->mCanUseHtmlEditor = false;
        $this->setCaption($this->mCaptionOrig);
    }

    /**
     * Prepare HTML for output
     *
     * @return  string  HTML
     */
    function render()
    {
        $field['id'] = $field['name'] = $this->getName();
        $field['value'] = $this->getValue();
        $field['width'] = $this->mWidth;
        $field['height'] = $this->mHeight;
        $field['cols'] = $this->getCols();
        $field['rows'] = $this->getRows();
        
        ob_start();
        if ($this->mUseHtmlEditor && $this->mCanUseHtmlEditor) {
            include NBFRAME_BASE_DIR.'/templates/xoopsform/NBFrameFormHtmlTextArea.tpl.php';
        } else {
            include NBFRAME_BASE_DIR.'/templates/xoopsform/NBFrameFormXcodeTextArea.tpl.php';
        }
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
}
?>
