<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class NBFrameFormTextDateSelect extends XoopsFormText
{
    function NBFrameFormTextDateSelect($caption, $name, $size = 15, $value= 0)
    {
        $value = !is_numeric($value) ? time() : intval($value);
        $this->XoopsFormText($caption, $name, $size, 25, $value);
    }

    function render()
    {
        $field['id'] = $field['name'] = $this->getName();
        $field['value'] = $this->getValue();
        $field['size'] = $this->getSize();
        $field['maxlength'] = $this->getMaxlength();
        $field['extra'] = $this->getExtra();
		$field['date'] = date("Y-m-d", $this->getValue());
        $field['js_date'] = formatTimestamp($this->getValue(), "'F j, Y H:i:s'");
        
        ob_start();
        include NBFRAME_BASE_DIR.'/templates/xoopsform/NBFrameFormTextDateSelect.tpl.php';
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
}
?>