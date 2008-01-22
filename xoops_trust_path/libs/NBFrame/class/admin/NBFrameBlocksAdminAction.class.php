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
if (!class_exists('NBFrameBlocksAdminAction')) {
    NBFrame::using('ObjectAction');
    
    class NBFrameBlocksAdminAction extends NBFrameObjectAction {
        var $block_arr;
        var $mObject;
        var $mObjects;
        var $mObjectForm;
        var $mObjectHandler;
        var $mName;
        var $mCaption;
        var $mErrorMsg = '';
        var $mGroupPermSysKeys = array('module_read', 'module_admin');
        var $mGroupPermModuleKeys = array();
        var $mGroupPermSysArray = array();
        var $mGroupPermModuleArray = array();
        var $mGroupPermBlockArray = array();

        function NBFrameBlocksAdminAction(&$environment) {
            parent::NBFrameObjectAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }

        function prepare() {
            $this->mObjectHandler =& NBFrame::getHandler('NBFrame.xoops.Block', NBFrame::null());

            parent::prepare('NBFrameBlocksAdmin','NBFrameBlocksAdmin',$this->__l('Block Admin'));

            $this->mDefaultOp = 'list';
            $this->mAllowedOp = array('list', 'edit', 'delete', 'deleteok', 'clone', 'order', 'save', 'insert','gperm');
            $this->mFormTemplate = 'admin/NBFrameAdminForm.html';
            $this->setObjectForm('NBFrame.admin.BlocksAdmin');
            $this->mListTemplate = 'admin/NBFrameAdminBlocksAdmin.html';
            
            if (($groupPermArray = $this->mEnvironment->getAttribute('ModuleGroupPermKeys'))&&is_array($groupPermArray)) {
                $this->mGroupPermModuleKeys = $groupPermArray;
            }
        }

        function executeListOp() {
            $mid = $GLOBALS['xoopsModule']->getVar('mid');
            $criteria =& new Criteria('mid', $mid);
            $criteria->setSort(array(
                                 array('sort'=>'visible', 'order'=>'DESC'),
                                 array('sort'=>'side', 'order'=>'ASC'),
                                 array('sort'=>'weight', 'order'=>'ASC'),
                              ));
            $this->mObjects =& $this->mObjectHandler->getObjects($criteria);
            
            $groupPermHandler =& NBFrame::getHandler('NBFrame.xoops.GroupPerm', NBFrame::null());

            foreach($this->mGroupPermSysKeys as $groupPermKey) {
                $this->mGroupPermSysArray[$groupPermKey] = $groupPermHandler->getGroupIds($groupPermKey, $mid, 1);
            }

            foreach($this->mGroupPermModuleKeys as $groupPermKey) {
                $this->mGroupPermModuleArray[$groupPermKey] = $groupPermHandler->getGroupIds($groupPermKey, 1, $mid);
            }
            
            foreach($this->mObjects as $object) {
                $bid = $object->getKey();
                $this->mGroupPermBlockArray[$bid]['groups'] = $groupPermHandler->getGroupIds('block_read', $bid, 1);
                $this->mGroupPermBlockArray[$bid]['name'] = $object->getVar('title');
            }
            
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }

        function executeGpermOp() {
            $mid = $GLOBALS['xoopsModule']->getVar('mid');
            $criteria =& new Criteria('mid', $mid);
            $this->mObjects =& $this->mObjectHandler->getObjects($criteria);

            $groupPermHandler =& NBFrame::getHandler('NBFrame.xoops.GroupPerm', NBFrame::null());
            foreach($this->mGroupPermSysKeys as $groupPermKey) {
                $this->mRequest->defParam('gperm_'.$groupPermKey, 'POST', 'array-int', array());
                $groups = $this->mRequest->getParam('gperm_'.$groupPermKey);
                $groupPermHandler->setRight($groupPermKey, $mid, $groups, 1);
            }
            foreach($this->mGroupPermModuleKeys as $groupPermKey) {
                $this->mRequest->defParam('gperm_'.$groupPermKey, 'POST', 'array-int', array());
                $groups = $this->mRequest->getParam('gperm_'.$groupPermKey);
                $groupPermHandler->setRight($groupPermKey, 1, $groups, $mid);
            }
            foreach($this->mObjects as $object) {
                $bid = $object->getKey();
                $this->mRequest->defParam('gperm_block_read_'.$bid, 'POST', 'array-int', array());
                $groups = $this->mRequest->getParam('gperm_block_read_'.$bid);
                $groupPermHandler->setRight('block_read', $bid, $groups, 1);
            }
            
            return NBFRAME_ACTION_SUCCESS;
        }

        function viewListOp() {
            if(!empty($this->mObjects)) {
                $this->_listblocks() ;
            }
            $this->_listgperm();
        }

        function viewFormOp() {
            parent::viewFormOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function viewDeleteOp() {
            parent::viewDeleteOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('errmsg', $this->mErrorMsg);
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function executeOrderOp() {
            foreach(array_keys($_POST['bid']) as $idx) {
                $idx = intval($idx);
                foreach($_POST as $key => $varArray) {
                    if (isset($varArray[$idx])) $rec[$key] = $varArray[$idx];
                }
                $object =& $this->mObjectHandler->get($idx);
                if(intval($rec['side']) == -1) {
                    $rec['visible'] = 0;
                    unset($rec['side']);
                } else {
                    $rec['visible'] = 1;
                }
                if (!isset($rec['modules'])) $rec['modules']=array();
                $object->setFormVars($rec,'');
                $this->mObjectHandler->insert($object,false,true);
            }
            if (!$this->mObjectHandler->hasError()) {
                return NBFRAME_ACTION_SUCCESS;
            } else {
                $this->mErrorMsg = $this->mObjectHandler->getErrors();
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeCloneOp() {
            if (isset($_GET[$this->mObjectKeyField])) {                $old_object =& $this->mObjectHandler->get(intval($_GET[$this->mObjectKeyField]));
                $object =& $this->mObjectHandler->create();
                $object->setVars($old_object->getVarArray('n'));
                return $this->_showForm($object, $this->__l('Clone'));
            } else {
                $this->mErrorMsg = $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeInsertOp() {
            $old_object =& $this->mObjectHandler->get(intval($_POST[$this->mObjectKeyField]));
            $block_type = $old_object->get('block_type') ;
            if( $block_type != 'C' && $block_type != 'M' && $block_type != 'D' ) {
                $this->mErrorMsg = $this->__e('Invalid block');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->create();
            $object->setVars($old_object->getVarArray('n'), true);
            $object->set('c_type', 1);
            $object->set('bid', 0);
            $object->set('block_type', $block_type == 'C' ? 'C' : 'D' );
            $object->set('func_num', 255);
            return $this->_insert($object, $this->__l('Clone'));
        }

        function _listBlocks()
        {
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar( 'name' ));
            $this->mXoopsTpl->assign('title', $this->__l('Blocks Admin'));
            $this->mXoopsTpl->assign('cachetimes', $this->mObjectHandler->getBlockCacheTimeListArray());
            $this->mXoopsTpl->assign('modulelist', $this->mObjectHandler->getModuleListArray());

            $blockConfigs = $GLOBALS['xoopsModule']->getInfo('blocks');

            $side_array = array('left'=>XOOPS_SIDEBLOCK_LEFT,
                          'cleft'=>XOOPS_CENTERBLOCK_LEFT,
                          'ccenter'=>XOOPS_CENTERBLOCK_CENTER,
                          'cright'=>XOOPS_CENTERBLOCK_RIGHT,
                          'right'=>XOOPS_SIDEBLOCK_RIGHT,
                          'none'=>-1);
            $this->mXoopsTpl->assign('side_array', $side_array);

            // blocks displaying loop
            foreach( array_keys( $this->mObjects ) as $i ) {
                $block = array();

                $block['weight'] = $this->mObjects[$i]->getVar('weight') ;
                $block['title'] = $this->mObjects[$i]->getVar('title') ;
                $block['name'] = $this->mObjects[$i]->getVar('name') ;
                $block['bcachetime'] = $this->mObjects[$i]->getVar('bcachetime') ;
                $block['bid'] = $this->mObjects[$i]->getVar('bid') ;
                $block['modules'] = $this->mObjects[$i]->getVar('modules') ;

                // visible and side
                if ( $this->mObjects[$i]->get('visible') != 1 ) {
                    foreach($side_array as $side) {
                        $block['ssel'][$side] = '';
                        $block['scol'][$side] = '#FFFFFF';
                    }
                    $block['ssel'][-1] = ' checked="checked"';
                    $block['scol'][-1] = '#FF0000';
                } else {
                    $block['ssel'][-1] = '';
                    $block['scol'][-1] = '#FFFFFF';
                    foreach($side_array as $side) {
                        if ($this->mObjects[$i]->get('side') == $side) {
                            $block['ssel'][$side] = ' checked="checked"';
                            $block['scol'][$side] = '#00FF00';
                        } else {
                            $block['ssel'][$side] = '';
                            $block['scol'][$side] = '#FFFFFF';
                        }
                    }
                }

                // delete link if it is cloned block
                if( $this->mObjects[$i]->get('block_type') == 'D' ||
                    $this->mObjects[$i]->get('block_type') == 'C' ) {
                    $block['can_delete']  = true;
                } else {
                    $block['can_delete']  = false;
                }

                // clone link if it is marked as cloneable block
                // $modversion['blocks'][n]['can_clone']
                if( $this->mObjects[$i]->get('block_type') == 'D' ||
                    $this->mObjects[$i]->get('block_type') == 'C' ) {
                    $block['can_clone'] = true ;
                } else {
                    $block['can_clone'] = false ;
                    foreach($blockConfigs as $blockConfig) {
                        if( $this->mObjects[$i]->get('show_func') == $blockConfig['show_func'] &&
                            $this->mObjects[$i]->get('func_file') == $blockConfig['file'] &&
                            (empty($blockConfig['template']) || $this->mObjects[$i]->get('template') == $blockConfig['template'])) {
                            if(!empty($blockConfig['can_clone'])) $block['can_clone'] = true ;
                        }
                    }
                }
                $blocks[] = $block;
            }
            $this->mXoopsTpl->assign('blocks', $blocks);
        }

        function _listgperm() {
            $gperm['sys'] = array();
            foreach($this->mGroupPermSysArray as $key=>$value) {
                $gperm['sys'][] = $this->_getGroupSelectList($key, $value);
            }
            $gperm['block'] = array();
            foreach($this->mGroupPermBlockArray as $key=>$value) {
                $gperm['block'][] = $this->_getGroupSelectList('block_read_'.$key, $value['groups'],$value['name'].' '.$this->__l('block show permission'));
            }
            $gperm['module'] = array();
            foreach($this->mGroupPermModuleArray as $key=>$value) {
                $gperm['module'][] = $this->_getGroupSelectList($key, $value);
            }
            $this->mXoopsTpl->assign('gperm', $gperm);
        }
        function _getGroupSelectList($name, $groups, $caption='') {
            if (empty($caption)) {
                $caption = $this->__l('Group Perm '.$name);
            }
            $element =& new XoopsFormSelectGroup($caption, 'gperm_'.$name, true, $groups, 5, true);
            $result = array('caption' => $caption, 'html'=>$element->render());
            unset($element);
            return $result;
        }
    }
}
?>
