<?php
$environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
$noCommonActions = $environment->getAttribute('NoCommonAction');
if (!is_array($noCommonActions)) {
    $noCommonActions = array();
}
$noCommonActions[] = 'NBFrame.GetModuleIcon';
$noCommonActions[] = 'NBFrame.GetImage';
$environment->setAttribute('NoCommonAction', $noCommonActions);
if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], $noCommonActions)) {
    require_once XOOPS_ROOT_PATH .'/include/common.php';
} else {
    foreach (array('GLOBALS', '_SESSION', 'HTTP_SESSION_VARS', '_GET', 'HTTP_GET_VARS', '_POST',
                   'HTTP_POST_VARS', '_COOKIE', 'HTTP_COOKIE_VARS', '_REQUEST', '_SERVER',
                   'HTTP_SERVER_VARS', '_ENV', 'HTTP_ENV_VARS', '_FILES', 'HTTP_POST_FILES') as $bad_global) {
        if (isset($_REQUEST[$bad_global])) {
           exit();
        }
    }
    if (class_exists('XCube_Root')) {
        $root=&XCube_Root::getSingleton();
        $root->mController->executeCommonSubset(true);

        $handler =& xoops_gethandler('config');
        $criteria =& new CriteriaCompo(new Criteria('conf_modid', 0));
        $criteria->add(new Criteria('conf_catid', XOOPS_CONF));
        $criteria->add(new Criteria('conf_name', 'language'));
        $configs =& $handler->getConfigs($criteria);
    
        if (count($configs) > 0) {
            $language = $configs[0]->get('conf_value', 'none');
        }

        $filename = XOOPS_MODULE_PATH . '/legacy/language/' . $language . '/charset_' . XOOPS_DB_TYPE . '.php';
        if (file_exists($filename)) {
            require_once($filename);
        }
    } else if (empty($GLOBALS['xoopsDB'])) {
        require_once XOOPS_ROOT_PATH.'/include/functions.php';
        require_once XOOPS_ROOT_PATH.'/class/errorhandler.php';
        require_once XOOPS_ROOT_PATH.'/class/logger.php';
        require_once XOOPS_ROOT_PATH.'/include/functions.php';
        require_once XOOPS_ROOT_PATH.'/class/database/databasefactory.php';
        require_once XOOPS_ROOT_PATH.'/kernel/object.php';
        require_once XOOPS_ROOT_PATH.'/class/criteria.php';
        require_once XOOPS_ROOT_PATH.'/class/module.textsanitizer.php';
        $GLOBALS['xoopsDB'] =& XoopsDatabaseFactory::getDatabaseConnection();
    }
}
?>
