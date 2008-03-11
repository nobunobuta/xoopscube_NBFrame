<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<a href="<?=XOOPS_URL?>"><?=$environment->__l('Home') ?></a>&nbsp;&raquo;&nbsp;
<a href="<?=$moduleTop?>"><?=$moduleName ?></a>
<? if (is_array($categoryParentPath)) {?>
<? foreach($categoryParentPath as $path) {?>
&nbsp;&raquo;&nbsp;<a href="<?=$environment->getActionUrl($actionName, array($keyName=>$path['key']))?>"><?=$path['name']?></a>
<? } ?>
&nbsp;&raquo;&nbsp;<a href = "<?=$environment->getActionUrl($actionName, array($keyName=>$category->getKey()))?>"><?=$category->getName();?></a>
<? } ?>
<?php ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
