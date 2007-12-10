<?php
$modEnv =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
$modversion['name'] = 'Simple';   // It'll be rewritten like "Simle [dirname]"
$modversion['version'] = '0.01';
$modversion['description'] = 'NBFrame Sample';
$modversion['credits'] = '';
$modversion['author'] = 'NobuNobu';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'images/logo.png';   // It'll be rewritten. Logo image must be placed in images directory.
$modversion['dirname'] = 'simple';   // It'll be rewritten with real dirname;

//If you want specify your custom install sequence, uncomment following 2 lines.
//$modversion['NBFrameOnInstall']['file'] =  '/include/oninstall.inc.php';
//$modversion['NBFrameOnInstall']['func'][] = 'onInstall';

//If you want specify your custom update sequence, uncomment following 2 lines.
//$modversion['NBFrameOnUpdate']['file'] = '/include/onupdate.inc.php';
//$modversion['NBFrameOnUpdate']['func'][] = 'onUpdate';

//If you want specify your custom uninstall sequence, uncomment following 2 lines.
//$modversion['NBFrameOnUninstall']['file'] =  '/include/onuninstall.inc.php';
//$modversion['NBFrameOnUninstall']['func'][] = 'onUninstall';

// Menu
$modversion['hasMain'] = 1;

// Do not specify a DB Table setting. because NBFrame uses include/tabledef.inc.php
//$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
//$modversion['tables'][0] = 'table';

// Do not specify a Module Template setting. because NBFrame scan /template directory
//$modversion['templates'][1]['file'] = 'main.html';
//$modversion['templates'][1]['description'] = '';

$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';   // It'll be rewritten
$modversion['adminmenu'] = 'include/admin_menu.inc.php'; // It'll be rewritten
$modversion['hasconfig'] = 1;
//$modversion['config'][1] = array(
//    'name'          => 'config1' ,
//    'title'         => '_MI_XXX_CONFIG1_MSG' ,
//    'description'   => '_MI_XXX_CONFIG1_DESC' ,
//    'formtype'      => 'textbox' ,
//    'valuetype'     => 'text' ,
//    'default'       => '' ,
//);

$modversion['blocks'][1]['file'] = 'NBFrameBlockLoader.php'; //You should specify this filename;
$modversion['blocks'][1]['name'] = 'Simple Block';
$modversion['blocks'][1]['description'] = '';
$modversion['blocks'][1]['show_func'] = 'b_simpleblock_show'; // It'll be rewritten with dirname prefix.
$modversion['blocks'][1]['edit_func'] = 'b_simpleblock_edit'; // It'll be rewritten with dirname prefix.
$modversion['blocks'][1]['options'] = '1';
$modversion['blocks'][1]['template'] = 'block_simple.html'; // It'll be rewritten
$modversion['blocks'][1]['can_clone'] = true ;
?>