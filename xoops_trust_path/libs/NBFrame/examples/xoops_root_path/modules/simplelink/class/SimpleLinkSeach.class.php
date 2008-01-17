<?php
if(! class_exists('SimpleLinkSeach')) {
    class SimpleLinkSeach {
        function search(&$environment, $queryarray, $andor, $limit, $offset, $userid)
        {
            $ret = array();
            $linkHandler =& NBFrame::getHandler('SimpleLinkLink', $environment);
            if ( is_array($queryarray) && $count = count($queryarray) ) {
                $criteria =& new CriteriaCompo(new Criteria('link_name', '%'.$queryarray[0].'%', 'LIKE'));
                for($i=1;$i<$count;$i++){
                    $criteria->add(new Criteria('link_name', '%'.$queryarray[$i].'%', 'LIKE'), $andor);
                }
            }
            $criteria->setLimit($limit);
            $criteria->setStart($offset);
            $linkObjects = $linkHandler->getObjects($criteria);
            foreach($linkObjects as $linkObject) {
                $ret[] = array(
                    'title' => $linkObject->getVar('link_name'),
                    'page' => $linkObject->getVar('link_name'),
                );
            }
            return $ret;
        }
    }
}
?>
