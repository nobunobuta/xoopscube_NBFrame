<?php $NBFrame_ini_short_open_tag = ini_set('short_open_tag', 'On');?>
<table cellspacing="1" class="outer">
<tr class="head" align="center">
<?php foreach($headers as $header) {?>
<?php if ($header['link']) {?>
<th width="<?=$header['width']?>px"><a href="<?=$header['link']?>" title="<?=$header['linktitle']?>" <?=$header['style']?>><?=$header['caption']?></a></th>
<?php }else{ ?>
<th width="<?=$header['width']?>px"><?=$header['caption']?></th>
<?php } ?>
<?php } ?>
</tr>
<?php $class='odd'; ?>
<?php foreach($records as $record) {?>
<tr class="<?=($class=($class=='odd')?'even':'odd')?>">
<?php foreach($record['items'] as $item) {?>
<?php if ($item['link']) {?>
<td align="<?=$item['align']?>"><a href="<?=$item['link']?>" title="<?=$item['linktitle']?>"><?=$item['value']?></a></td>
<?php }else{ ?>
<td align="<?=$item['align']?>"><?=$item['value']?></td>
<?php } ?>
<?php } ?>
</tr>
<?php } ?>
</table>
<?=$pagenav ?>
<?php ini_set('short_open_tag', $NBFrame_ini_short_open_tag);?>
