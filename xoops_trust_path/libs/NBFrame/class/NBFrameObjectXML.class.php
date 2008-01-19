<?php
/**
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectXML')) {
    class NBFrameObjectXML {
        var $mEnvironment;
        var $mRoot;
        var $mElements;

        function NBFrameObjectXML($environment) {
            $this->mLanguage =& NBFrame::getLanguageManager();
            $this->mEnvironment = $environment;
        }
        
        function prepare($root='objects') {
            $this->mRoot = $root;
        }
        
        function addElement($name,$tag="") {
            if (empty($tag)) $tag=$name;
            $this->mElements[$name] = $tag;
        }

        function &getXMLItems(&$object) {
            $xml = "";
            foreach($this->mElements as $name=>$tag) {
                $extItemMethod = 'extraItem_'.$name;
                $formatMethod = 'formatItem_'.$name;
                $item = array();
                if(method_exists($this, $extItemMethod)) {
                   $value = $object->getVar($name);
                    $item = array_merge($item, $this->$extItemMethod($object,$value));
                } else if(method_exists($this, $formatMethod)) {
                    $value = $this->$formatMethod($object->getVar($name));
                } else {
                    $value = $object->getVar($name);
                }
                $xml.="<".$tag;
                foreach($item as $attrib=>$attrib_val) {
                    $xml .= " ".$attrib."=".$attrib_val;
                }

                $xml .= ">".$value."</".$tag.">\n";
                $items[] = $item;
            }
            return $xml;
        }
        function putXMLHeader() {
            header('Content-type: application/xml;charset=EUC-JP"');
            echo '<?xml version="1.0" encoding="'._CHARSET.'"?>'."\n";
        }

        function getXML(&$objects, $sort='', $order='') {
            $xml = "<".$this->mRoot."><items>";
            foreach ($objects as $object) {
                $xml .= "<item>\n".$this->getXMLItems($object)."</item>\n";
            }
            $xml .= "</items></".$this->mRoot.">";
            return $xml;
        }
    }
}
?>
