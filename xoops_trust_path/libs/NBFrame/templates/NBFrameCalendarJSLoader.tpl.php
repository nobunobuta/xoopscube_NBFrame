<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
if (NBFrameCalendar_calendar_js_src == undefined) {
    var NBFrameCalendar_calendar_js_src = '<?=XOOPS_URL?>/include/calendar.js';
    document.write('<'+'script src=\"' + NBFrameCalendar_calendar_js_src+'\"'+' type=\"text/javascript\"><'+'/script>');

    document.write('<'+'script src=\"<?=$url?>"'+' type=\"text/javascript\"><'+'/script>');

    var NBFrameCalendar_calendar_css_src = '<?=XOOPS_URL?>/include/calendar-blue.css';
    document.write('<'+'style>@import \"'+ NBFrameCalendar_calendar_css_src + '\";<'+'/style>');
}
<?php ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
