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
if(! class_exists('SimpleLinkSearch')) {
    class SimpleLinkSearch {
        function search(&$environment, $queryarray, $andor, $limit, $offset, $userid)
        {
            $ret = array();
            $linkHandler =& NBFrame::getHandler('SimpleLinkLink', $environment);
            if ( is_array($queryarray) && $count = count($queryarray) ) {
                $criteria =& new CriteriaCompo(new Criteria('link_name', '%'.$queryarray[0].'%', 'LIKE'));
                for($i=1;$i<$count;$i++){
                    $criteria->add(new Criteria('link_name', '%'.$queryarray[$i].'%', 'LIKE'), $andor);
                }
            } else {
                if ($userid) {
                    return array();
                }
                $criteria =& new CriteriaCompo(new CriteriaElement());
            }
            $criteria->setLimit($limit);
            $criteria->setStart($offset);
            $linkObjects = $linkHandler->getObjects($criteria);
            foreach($linkObjects as $linkObject) {
                $ret[] = array(
                    'title' => $linkObject->getVar('link_name'),
                    'page' => $linkObject->getVar('link_name'),
                    'uid' => 0,
                );
            }
            return $ret;
        }
    }
}
?>
