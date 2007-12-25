<?php
if (class_exists('NBFrame')) {
    $environment->setAttribute('ModueleMainAction','SimpleLinkDefault');
    $environment->setAttribute('AdminMainAction',  'admin.SimpleLinkLinkAdmin');

    $environment->setAttribute('AllowedAction', array('SimpleLinkDefault',
                                                      'admin.SimpleLinkLinkAdmin',
                                                      'admin.SimpleLinkCategoryAdmin',
                                                     ));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseTemplateAdmin', true);
}
?>
