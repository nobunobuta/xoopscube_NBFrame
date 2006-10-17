<?php
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

        function NBFrameBlocksAdminAction(&$environment) {
            parent::NBFrameObjectAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }

        function prepare() {
            NBFrameAction::prepare();
            $this->mDefaultOp = 'list';
            $this->mAllowedOp = array('list', 'edit', 'delete', 'deleteok', 'clone', 'order', 'save', 'insert',);
            $this->mFormTemplate = 'NBFrameAdminForm.html';
            $this->setObjectForm('NBFrame.admin.BlocksAdmin');
            $this->mListTemplate = 'NBFrameAdminBlocksAdmin.html';
            $this->mName = 'NBFrameBlocksAdmin';
            $this->mCaption = $this->__l('Block Admin');
            NBFrame::using('xoops.Block');
            $NullEnv = null;
            $this->mObjectHandler =& NBFrame::getHandler('NBFrameBlock', $NullEnv);
            $this->setObjectKeyField();
        }

        function executeListOp() {
            $criteria =& new Criteria('mid', $GLOBALS['xoopsModule']->getVar( 'mid' ));
            $criteria->setSort(array(
                                 array('sort'=>'visible', 'order'=>'DESC'),
                                 array('sort'=>'side', 'order'=>'ASC'),
                                 array('sort'=>'weight', 'order'=>'ASC'),
                              ));
            $this->mObjects =& $this->mObjectHandler->getObjects($criteria);
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }

        function viewListOp() {
            if(!empty($this->mObjects)) {
                $this->_listblocks() ;
            }
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
                $object->setFormVars($rec,'');
                if ($this->mObjectHandler->insert($object,false,true)) {
                    return NBFRAME_ACTION_SUCCESS;
                } else {
                    $this->mErrorMsg = $this->mObjectHandler->getErrors();
                    return NBFRAME_ACTION_ERROR;
                }
            }
        }

        function executeCloneOp() {
            if (isset($_GET[$this->mObjectKeyField])) {                $old_object =& $this->mObjectHandler->get(intval($_GET[$this->mObjectKeyField]));
                $object =& $this->mObjectHandler->create();
                $object->setVars($old_object->getVarArray('n'), true);
                return $this->_showForm($object, $this->__l('Clone'));
            } else {
                $this->mErrorMsg = $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeInsertOp() {
            $old_object =& $this->mObjectHandler->get(intval($_POST[$this->mObjectKeyField]));
            $block_type = $old_object->getVar('block_type') ;
            if( $block_type != 'C' && $block_type != 'M' && $block_type != 'D' ) {
                $this->mErrorMsg = $this->__e('Invalid block');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->create();
            $object->setVars($old_object->getVarArray('n'), true);
            $object->setVar('bid', 0);
            $object->setVar('block_type', $block_type == 'C' ? 'C' : 'D' );
            $object->setVar('func_num', 255);
            return $this->_insert($object, $this->__l('Clone'));
        }

        function _listBlocks()
        {
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar( 'name' ));
            $this->mXoopsTpl->assign('title', $this->__l('Blocks Admin'));
            // cachetime options
            $cachetimes = array('0' => _NOCACHE,
                                '30' => sprintf(_SECONDS, 30),
                                '60' => _MINUTE,
                                '300' => sprintf(_MINUTES, 5),
                                '1800' => sprintf(_MINUTES, 30),
                                '3600' => _HOUR,
                                '18000' => sprintf(_HOURS, 5),
                                '86400' => _DAY,
                                '259200' => sprintf(_DAYS, 3),
                                '604800' => _WEEK,
                                '2592000' => _MONTH);
                                
            $this->mXoopsTpl->assign('cachetimes', $cachetimes);
            
            NBFrame::using('xoops.Module');
            $NullEnv = null;
            $moduleHandler =& NBFrame::getHandler('NBFrameModule', $NullEnv);

            $criteria = new CriteriaCompo(new Criteria('hasmain', 1));
            $criteria->add(new Criteria('isactive', 1));
            $moduleList =& $moduleHandler->getSelectOptionArray($criteria);
            $moduleList[-1] = $this->__L('Top Page');
            $moduleList[0] = $this->__L('All Pages');
            ksort($moduleList);
            $this->mXoopsTpl->assign('modulelist', $moduleList);
            $blockConfigs = $GLOBALS['xoopsModule']->getInfo('blocks');

            // blocks displaying loop
            foreach( array_keys( $this->mObjects ) as $i ) {
                $block = array();

                $block['weight'] = $this->mObjects[$i]->getVar("weight") ;
                $block['title'] = $this->mObjects[$i]->getVar("title") ;
                $block['name'] = $this->mObjects[$i]->getVar("name") ;
                $block['bcachetime'] = $this->mObjects[$i]->getVar("bcachetime") ;
                $block['bid'] = $this->mObjects[$i]->getVar("bid") ;
                $block['modules'] = $this->mObjects[$i]->getVar("modules") ;

                // visible and side
                $block['side'] = array('left'=>XOOPS_SIDEBLOCK_LEFT,
                                       'cleft'=>XOOPS_CENTERBLOCK_LEFT,
                                       'ccenter'=>XOOPS_CENTERBLOCK_CENTER,
                                       'cright'=>XOOPS_CENTERBLOCK_RIGHT,
                                       'right'=>XOOPS_SIDEBLOCK_RIGHT,
                                       'none'=>-1);

                if ( $this->mObjects[$i]->getVar("visible") != 1 ) {
                    foreach($block['side'] as $side) {
                        $block['ssel'][$side] = '';
                        $block['scol'][$side] = '#FFFFFF';
                    }
                    $block['ssel'][-1] = ' checked="checked"';
                    $block['scol'][-1] = '#FF0000';
                } else {
                    $block['ssel'][-1] = '';
                    $block['scol'][-1] = '#FFFFFF';
                    foreach($block['side'] as $side) {
                        if ($this->mObjects[$i]->getVar("side") == $side) {
                            $block['ssel'][$side] = ' checked="checked"';
                            $block['scol'][$side] = '#00FF00';
                        } else {
                            $block['ssel'][$side] = '';
                            $block['scol'][$side] = '#FFFFFF';
                        }
                    }
                }

                // delete link if it is cloned block
                if( $this->mObjects[$i]->getVar("block_type") == 'D' ||
                    $this->mObjects[$i]->getVar("block_type") == 'C' ) {
                    $block['can_delete']  = true;
                } else {
                    $block['can_delete']  = false;
                }

                // clone link if it is marked as cloneable block
                // $modversion['blocks'][n]['can_clone']
                if( $this->mObjects[$i]->getVar("block_type") == 'D' ||
                    $this->mObjects[$i]->getVar("block_type") == 'C' ) {
                    $block['can_clone'] = true ;
                } else {
                    $block['can_clone'] = false ;
                    foreach($blockConfigs as $blockConfig) {
                        if( $this->mObjects[$i]->getVar("show_func") == $blockConfig['show_func'] &&
                            $this->mObjects[$i]->getVar("func_file") == $blockConfig['file'] &&
                            (empty($blockConfig['template']) || $this->mObjects[$i]->getVar("template") == $blockConfig['template'])) {
                            if(!empty($blockConfig['can_clone'])) $block['can_clone'] = true ;
                        }
                    }
                }
                $blocks[] = $block;
            }
            $this->mXoopsTpl->assign('blocks', $blocks);
        }
    }
}
?>
