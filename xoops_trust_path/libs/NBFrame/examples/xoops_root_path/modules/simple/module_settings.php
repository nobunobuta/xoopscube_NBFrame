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
    $environment->setAttribute('ModueleMainAction','SimpleDefault');             //Default Action Name
    $environment->setAttribute('AdminMainAction',  'admin.SimpleTableAdmin');

//    $environment->setAttribute('MainMenu', 'SimpleMainMenu');
//    $environment->setAttribute('AdminMenu', 'admin.SimpleAdminMenu');

    $environment->setAttribute('AllowedAction', array('SimpleDefault',
                                                      'SimpleNext',
                                                      'admin.SimpleTableAdmin',
                                                     ));

//  $environment->setAttribute('ModuleGroupPermKeys', array('can_usehtml'));

    $environment->setAttribute('UseAltSys', true);
    $environment->setAttribute('UseBlockAdmin', true);
    $environment->setAttribute('UseTemplateAdmin', true);

//    $environment->setAttribute('UseD3ForumComment', true);

//    $environment->setAttribute('StaticUrlMode', true);
//    $environment->setAttribute('ModRewriteOff', true);
}
?>
