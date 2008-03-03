<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<? if (file_exists(XOOPS_ROOT_PATH.'/common/fckeditor/fckeditor.js')) { ?>
<script type="text/javascript" src="<?=XOOPS_URL?>/common/fckeditor/fckeditor.js" ></script>
<textarea id="<?=$field['id']?>" name="<?=$field['name']?>"><?=$field['value']?></textarea>
<script>
//<![CDATA[
var NBFrameFCKeditor_<?=$field['id']?> = new FCKeditor("<?=$field['id']?>", "<?=$field['width']?>", "<?=$field['height']?>" , 'Default');
    NBFrameFCKeditor_<?=$field['id']?>.BasePath = '<?=XOOPS_URL?>/common/fckeditor/';
    NBFrameFCKeditor_<?=$field['id']?>.ReplaceTextarea();
//]]>
</script>
<? } else { ?>
<textarea id="<?=$field['id']?>" name="<?=$field['name']?>" cols="80" rows="25"><?=$field['value']?></textarea>
<? } ?>
<? ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
