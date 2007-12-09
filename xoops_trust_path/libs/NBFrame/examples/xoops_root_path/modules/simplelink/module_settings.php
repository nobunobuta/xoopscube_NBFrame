<?php
if (class_exists('NBFrame')) {
    $environment->setAttribute('ModueleMainAction','SimpleLinkDefault');
    $environment->setAttribute('AdminMainAction',  'admin.SimpleLinkMain');

    $environment->setAttribute('AllowedAction', array('SimpleLinkDefault',
                                                      'admin.SimpleLinkMain',
                                                     ));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseTemplateAdmin', true);
}
?>
