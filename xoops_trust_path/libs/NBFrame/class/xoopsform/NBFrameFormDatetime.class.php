<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class NBFrameFormDatetime extends XoopsFormElementTray
{
    var $_mDateElement;
    var $_mTimeElement;

    function NBFrameFormDatetime($caption, $name, $size = 15, $value=0)
    {
        NBFrame::using('xoopsform.FormTextDateSelect');
        $this->XoopsFormElementTray($caption, '&nbsp;');
        
        $this->_mDateElement =& new NBFrameFormTextDateSelect('', $name.'[date]', $size);
        $this->_mTimeElement =& new XoopsFormSelect('', $name.'[time]');

        $this->addElement($this->_mDateElement);
        $this->addElement($this->_mTimeElement);

        $timearray = array();
        for ($i = 0; $i < 24; $i++) {
            for ($j = 0; $j < 60; $j = $j + 10) {
                $key = ($i * 3600) + ($j * 60);
                $timearray[$key] = ($j != 0) ? $i.':'.$j : $i.':0'.$j;
            }
        }
        ksort($timearray);
        $this->_mTimeElement->addOptionArray($timearray);

        $this->setValue($value);
    }

    function setValue($value=0) {
        $value = (intval($value) > 0) ? intval($value) : time();
        
        $datetime = getdate(NBFrame::convServerToLocalTime($value));
        $timevalue=$datetime['hours'] * 3600 + 600 * ceil($datetime['minutes'] / 10);

        $this->_mDateElement->setValue($value);
        $this->_mTimeElement->_value = array();
        $this->_mTimeElement->setValue($timevalue);
    }
}
?>