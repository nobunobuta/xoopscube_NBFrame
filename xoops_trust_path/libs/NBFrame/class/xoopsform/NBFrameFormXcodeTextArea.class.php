<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formtextarea.php";
class NBFrameFormXcodeTextArea extends XoopsFormTextArea
{
    var $mWidth;
    var $mHeight;
    /**
     * Constructor
     *
     * @param   string  $caption    Caption
     * @param   string  $name       "name" attribute
     * @param   string  $value      Initial text
     * @param   str     $rows       width param
     * @param   str     $cols       height param
     */
    function NBFrameFormXcodeTextArea($caption, $name, $value='', $width='100%', $height='500px')
    {
        $this->XoopsFormTextArea($caption, $name, $value);
        $this->mWidth = $width;
        $this->mHeight = $height;
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
        
        ob_start();
        include NBFRAME_BASE_DIR.'/templates/xoopsform/NBFrameFormXCodeTextArea.tpl.php';
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
}
?>
