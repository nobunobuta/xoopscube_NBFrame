<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<?php $environment =& NBFrame::getEnvironment(); ?>
<script src="<?=$environment->getActionUrl('NBFrame.LoadCalendarJS', array('lang'=>$GLOBALS['xoopsConfig']['language']))?>" type="text/javascript"></script>
<input type="text" name="<?=$field['name']?>" id="<?=$field['id']?>" size="<?=$field['size']?>" maxlength="<?=$field['maxlength']?>" value="<?=$field['date']?>" <?=$field['extra']?> />
<input type="reset" value=" ... " onclick="return NBFrameCalendarShow('<?=$field['name']?>',<?=$field['js_date']?>);" />
<?php ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
