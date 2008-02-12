<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (class_exists('NBFrame')) {
    $environment->setAttribute('ModueleMainAction','SimpleLinkDefault');
    $environment->setAttribute('AdminMainAction',  'admin.SimpleLinkLinkAdmin');
//  $environment->setAttribute('MainMenu',  'SimpleLinkMainMenu');
    $environment->setAttribute('AdminMenu',  'admin.SimpleLinkAdminMenu');

    $environment->setAttribute('AllowedAction', array('SimpleLinkDefault',
                                                      'admin.SimpleLinkLinkAdmin',
                                                      'admin.SimpleLinkCategoryAdmin',
                                                     ));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseTemplateAdmin', true);
}
?>
