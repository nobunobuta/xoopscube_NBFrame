<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<? if (file_exists(XOOPS_ROOT_PATH.'/common/fckeditor/xcode.config.js')) { ?>
<script type="text/javascript" src="<?=XOOPS_URL?>/common/fckeditor/fckeditor.js" ></script>
<textarea id="<?=$field['id']?>" name="<?=$field['name']?>"><?=$field['value']?></textarea>
<script>
//<![CDATA[
var NBFrameFCKeditor_<?=$field['id']?> = new FCKeditor("<?=$field['id']?>", "<?=$field['width']?>", "<?=$field['height']?>");
    NBFrameFCKeditor_<?=$field['id']?>.BasePath = '<?=XOOPS_URL?>/common/fckeditor/';
    NBFrameFCKeditor_<?=$field['id']?>.Config['CustomConfigurationsPath']='<?=XOOPS_URL?>/common/fckeditor/xcode.config.js';
    NBFrameFCKeditor_<?=$field['id']?>.ReplaceTextarea();
//]]>
</script>
<? } else {
	$element =& new XoopsFormDhtmlTextArea($field['name'], $field['name'], $field['value'], 25, 80);
	$element->setId($field['id']);
	echo $element->render();
} ?>
<? ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
