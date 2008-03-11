<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<? if (file_exists(XOOPS_ROOT_PATH.'/common/fckeditor/xoops/FCKXoops.js')) { ?>
<textarea id="<?=$field['id']?>" name="<?=$field['name']?>" rows="<?=$field['rows']?>" cols="<?=$field['cols']?>" ><?=$field['value']?></textarea>
<script>
//<![CDATA[
if (FCKXcodeBaseUrl == undefined) { 
  document.write('<'+'script type="text/javascript" src="<?=XOOPS_URL?>/common/fckeditor/fckeditor.js" ><'+'/script>');
  document.write('<'+'script type="text/javascript" src="<?=XOOPS_URL?>/common/fckeditor/xoops/FCKXoops.js" ><'+'/script>');
}
  var FCKXcodeBaseUrl='<?=XOOPS_URL?>';
  <? if (is_object($GLOBALS['xoopsUser'])) { ?>var FCKCanUpload=true;<? }else{ ?>var FCKCanUpload=false;<? } ?>
  var FCKCanSwitchMode=true;
  var FCKCanUseHTML=true;
</script>
<script>DHTMLWysiWyg('<?=$field['id']?>','html',<?=$field['rows']?>);</script>
<? } else {
	$element =& new XoopsFormDhtmlTextArea($field['name'], $field['name'], $field['value'], 25, 80);
	$element->setId($field['id']);
	echo $element->render();
} ?>
<? ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
