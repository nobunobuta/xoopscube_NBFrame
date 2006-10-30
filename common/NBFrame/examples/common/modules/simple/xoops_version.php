<?php
$modEnv =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
$modversion['name'] = 'Simple ['.$modEnv->mDirName.']';
$modversion['version'] = '0.01';
$modversion['description'] = 'NBFrame Sample';
$modversion['credits'] = '';
$modversion['author'] = 'NobuNobu';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'images/logo.png';
$modversion['dirname'] = $modEnv->mDirName;

//$modversion['NBFrameOnInstall']['file'] =  '/include/oninstall.inc.php';
//$modversion['NBFrameOnInstall']['func'][] = 'onInstall';
//$modversion['NBFrameOnUpdate']['file'] = '/include/onupdate.inc.php';
//$modversion['NBFrameOnUpdate']['func'][] = 'onUpdate';
//$modversion['NBFrameOnUninstall']['file'] =  '/include/onuninstall.inc.php';
//$modversion['NBFrameOnUninstall']['func'][] = 'onUninstall';

NBFrame::prepareInstaller($modversion);

// Menu
$modversion['hasMain'] = 1;

// DB Table
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = $modEnv->prefix('table');

// Templates

$modversion['templates'][1] = NBFrame::setModuleTemplate('main.html');
$modversion['templates'][1]['description'] = '';

$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'index.php?action='.$modEnv->getAttribute('AdminMainAction');
$modversion['adminmenu'] = 'include/NBFrameAdminMenu.inc.php';
$modversion['hasconfig'] = 1;
//$modversion['config'][1] = array(
//    'name'          => 'config1' ,
//    'title'         => '_MI_XXX_CONFIG1_MSG' ,
//    'description'   => '_MI_XXX_CONFIG1_DESC' ,
//    'formtype'      => 'textbox' ,
//    'valuetype'     => 'text' ,
//    'default'       => '' ,
//);

$modversion['blocks'][1]['file'] = 'NBFrameBlockLoader.php';
$modversion['blocks'][1]['name'] = 'Simple Block';
$modversion['blocks'][1]['description'] = '';
$modversion['blocks'][1]['show_func'] = NBFrame::getBlockShowFunction('simpleblock');
$modversion['blocks'][1]['edit_func'] = NBFrame::getBlockEditFunction('simpleblock');
$modversion['blocks'][1]['options'] = '1';
$modversion['blocks'][1] += NBFrame::setBlockTemplate('block_simple.html');
$modversion['blocks'][1]['can_clone'] = true ;
?>
