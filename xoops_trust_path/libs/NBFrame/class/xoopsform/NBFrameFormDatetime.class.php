<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class NBFrameFormDatetime extends XoopsFormElementTray
{
    var $_mDateElement;
    var $_mHourElement;
    var $_mMinuteElement;

    function NBFrameFormDatetime($caption, $name, $size = 15, $value=0)
    {
        NBFrame::using('xoopsform.FormTextDateSelect');
        $this->XoopsFormElementTray($caption, '&nbsp;');
        
        $this->_mDateElement =& new NBFrameFormTextDateSelect('', $name.'[date]', $size);
        $this->_mHourElement =& new XoopsFormSelect('', $name.'[hour]');
        $this->_mMinuteElement =& new XoopsFormSelect('', $name.'[minute]');

        $this->addElement($this->_mDateElement);
        $this->addElement($this->_mHourElement);
        $this->addElement($this->_mMinuteElement);

        $hourarray = array();
        for ($i = 0; $i < 24; $i++) {
            $hourarray[$i*3600] = sprintf("%02d", $i);
        }
        $this->_mHourElement->addOptionArray($hourarray);

        $minutearray = array();
        for ($i = 0; $i < 60; $i = $i + 5) {
            $minutearray[$i*60] = sprintf("%02d", $i);
        }
        $this->_mMinuteElement->addOptionArray($minutearray);

        $this->setValue($value);
    }

    function setValue($value=0) {
        $value = ((intval($value)) > 0) ? intval($value) : time();
        $datetime = getdate(NBFrame::convServerToLocalTime($value));
        $hourvalue = $datetime['hours'] * 3600;
        $minutevalue = 300 * (ceil($datetime['minutes'] / 5));

        $this->_mDateElement->setValue($value);
        $this->_mHourElement->_value = array();
        $this->_mHourElement->setValue($hourvalue);
        $this->_mMinuteElement->_value = array();
        $this->_mMinuteElement->setValue($minutevalue);
    }
}
?>