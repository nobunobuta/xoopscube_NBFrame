<?php
if (class_exists('NBFrame')) {
    $environment->setAttribute('ModueleMainAction','SimpleDefault');
    $environment->setAttribute('AdminMainAction',  'admin.SimpleTable0dmin');

    $environment->setAttribute('AllowedAction', array('SimpleDefault',
                                                      'SimpleNext',
                                                      'admin.SimpleTableAdmin',
                                                     ));

    $environment->setAttribute('BlockHandler', array('simpleblock'));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseBlockAdmin', true);
    $environment->setAttribute('UseTemplateAdmin', true);
}
?>
