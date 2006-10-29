<?php
if (class_exists('NBFrame')) {
    $environment->setAttribute('ModueleMainAction','SimpleDefault');
    $environment->setAttribute('AdminMainAction',  'admin.SimpleTable0Admin');

    $environment->setAttribute('AllowedAction', array('SimpleDefault',
                                                          'admin.SimpleTable0Admin',
                                                          'admin.SimpleTable1Admin',
                                                          'NBFrame.admin.BlocksAdmin',
                                                          'NBFrame.admin.AltSys',
                                                       ));

    $environment->setAttribute('BlockHandler', array('simpleblock'));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseBlockAdmin', true);
    $environment->setAttribute('UseTemplateAdmin', true);
}
?>
