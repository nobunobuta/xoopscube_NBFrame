<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
if (_NBFrameCalendar_ == undefined) {
    var _NBFrameCalendar_ = null;

    function NBFrameCalendarSelected(cal, date) {
      cal.sel.value = date;
    }

    function NBFrameCalendarCloseHandler(cal) {
      cal.hide();
      Calendar.removeEvent(document, 'mousedown', NBFrameCalendarCheck);
    }

    function NBFrameCalendarCheck(ev) {
      var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
      for (; el != null; el = el.parentNode)
        if (el == _NBFrameCalendar_.element || el.tagName == 'A') break;
      if (el == null) {
        _NBFrameCalendar_.callCloseHandler(); Calendar.stopEvent(ev);
      }
    }
    function NBFrameCalendarShow(id, value) {
      var el = xoopsGetElementById(id);
      if (_NBFrameCalendar_ != null) {
        _NBFrameCalendar_.hide();
      } else {
        var cal = new Calendar(true, value, NBFrameCalendarSelected, NBFrameCalendarCloseHandler);
        _NBFrameCalendar_ = cal;
        cal.setRange(2000, 2025);
        _NBFrameCalendar_.create();
      }
      _NBFrameCalendar_.sel = el;
      _NBFrameCalendar_.parseDate(el.value);
      _NBFrameCalendar_.showAtElement(el);
      Calendar.addEvent(document, 'mousedown', NBFrameCalendarCheck);
      return false;
    }

    Calendar._DN = new Array
    ('<?=_CAL_SUNDAY?>',
     '<?=_CAL_MONDAY?>',
     '<?=_CAL_TUESDAY?>',
     '<?=_CAL_WEDNESDAY?>',
     '<?=_CAL_THURSDAY?>',
     '<?=_CAL_FRIDAY?>',
     '<?=_CAL_SATURDAY?>',
     '<?=_CAL_SUNDAY?>');
    Calendar._MN = new Array
    ('<?=_CAL_JANUARY?>',
     '<?=_CAL_FEBRUARY?>',
     '<?=_CAL_MARCH?>',
     '<?=_CAL_APRIL?>',
     '<?=_CAL_MAY?>',
     '<?=_CAL_JUNE?>',
     '<?=_CAL_JULY?>',
     '<?=_CAL_AUGUST?>',
     '<?=_CAL_SEPTEMBER?>',
     '<?=_CAL_OCTOBER?>',
     '<?=_CAL_NOVEMBER?>',
     '<?=_CAL_DECEMBER?>');

    Calendar._TT = {};
    Calendar._TT['TOGGLE'] = '<?=_CAL_TGL1STD?>';
    Calendar._TT['PREV_YEAR'] = '<?=_CAL_PREVYR?>';
    Calendar._TT['PREV_MONTH'] = '<?=_CAL_PREVMNTH?>';
    Calendar._TT['GO_TODAY'] = '<?=_CAL_GOTODAY?>';
    Calendar._TT['NEXT_MONTH'] = '<?=_CAL_NXTMNTH?>';
    Calendar._TT['NEXT_YEAR'] = '<?=_CAL_NEXTYR?>';
    Calendar._TT['SEL_DATE'] = '<?=_CAL_SELDATE?>';
    Calendar._TT['DRAG_TO_MOVE'] = '<?=_CAL_DRAGMOVE?>';
    Calendar._TT['PART_TODAY'] = '(<?=_CAL_TODAY?>)';
    Calendar._TT['MON_FIRST'] = '<?=_CAL_DISPM1ST?>';
    Calendar._TT['SUN_FIRST'] = '<?=_CAL_DISPS1ST?>';
    Calendar._TT['CLOSE'] = '<?php echo _CLOSE;?>';
    Calendar._TT['TODAY'] = '<?=_CAL_TODAY?>';

    // date formats
    Calendar._TT['DEF_DATE_FORMAT'] = 'y-mm-dd';
    Calendar._TT['TT_DATE_FORMAT'] = 'y-mm-dd';

    Calendar._TT['WK'] = '';
    //-->
}
<?php ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
