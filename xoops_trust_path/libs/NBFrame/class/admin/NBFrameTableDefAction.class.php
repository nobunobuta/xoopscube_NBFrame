<?php
if (!class_exists('NBFrameTableDefAction')) {
    NBFrame::using('AdminAction');
    
    class NBFrameTableDefAction extends NBFrameAdminAction {
        var $mFieldDefArr = array();
        var $mPrimaryKeys = array();
        var $mKeys = array();
        var $mUniqueKeys = array();
        var $mTableName = '';

        function prepare() {
            parent::prepare();
            $this->mRequest->defParam('table', '', 'var', '');
            $this->mRequest->defParam('realname', '', 'string-yn', 'N');
        }
        function executeDefaultOp() {
            if ($this->mTableName = $this->mRequest->getParam('table')) {
                if ($this->mRequest->getParam('realname') == 'N') {
                    $tableName = $GLOBALS['xoopsDB']->prefix($this->prefix($this->mTableName));
                } else {
                    $tableName = $GLOBALS['xoopsDB']->prefix($this->mTableName);
                }
                $sql = 'SHOW COLUMNS FROM `'.$tableName.'`';
                $results = $GLOBALS['xoopsDB']->queryF($sql);
                while($row = $GLOBALS['xoopsDB']->fetchArray($results)) {
                    $this->mFieldDefArr[] = $row;
                    unset($row);
                }

                $sql = 'SHOW INDEX FROM `'.$tableName.'`';
                $results = $GLOBALS['xoopsDB']->queryF($sql);
                while($row = $GLOBALS['xoopsDB']->fetchArray($results)) {
                    if($row['Key_name'] == 'PRIMARY') {
                       $this->mPrimaryKeys[$row['Seq_in_index']-1] = $row['Column_name'];
                    } else if ($row['Non_unique'] == 1) {
                       $this->mKeys[$row['Key_name']][$row['Seq_in_index']-1] = $row['Column_name'];
                    } else {
                       $this->mUniqueKeys[$row['Key_name']][$row['Seq_in_index']-1] = $row['Column_name'];
                    }
                    unset($row);
                }
            }
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }
        function viewDefaultOp() {
            echo '<pre>';
            echo '    $tableDef[\''.$this->mOrigDirName.'\'][\''.$this->mTableName.'\'] = array('."\n";
            echo '        \'fields\' => array('."\n";
            $delim = '';
            $useSysField = false;
            foreach($this->mFieldDefArr as $fieldDef) {
                if (preg_match('/^_NBsys_/',$fieldDef['Field'])) {
                    $useSysField = true;
                } else {
                    echo $delim.'            \''.$fieldDef['Field'].'\' => array(';
                    echo '\''.$fieldDef['Type'].'\', ';
                    if ($fieldDef['Null'] == 'YES') {
                        echo '\'NULL\', ';
                    } else {
                        echo '\'NOT NULL\', ';
                    }
                    if ($fieldDef['Default'] !== NULL) {
                        echo '\''.$fieldDef['Default'].'\', ';
                    } else {
                        echo 'null, ' ;
                    }
                    echo '\''.$fieldDef['Extra'].'\'';
                    echo ')';
                    $delim = ','."\n";
                }
            }
            echo "\n".'        )';
            if (count($this->mPrimaryKeys)>0) {
                echo ','."\n".'        \'primary\' => \'';
                $delim = '';
                for ($i=0; $i<count($this->mPrimaryKeys); $i++) {
                    echo $delim.$this->mPrimaryKeys[$i];
                    $delim = ',';
                }
                echo '\'';
            }
            if (count($this->mKeys)>0) {
                echo ','."\n".'        \'keys\' => array('."\n";
                $delim1 = '';
                foreach($this->mKeys as $name=>$key) {
                    echo $delim1.'            \''.$name.'\' => \'';
                    $delim2 = '';
                    for ($i=0; $i<count($key); $i++) {
                        echo $delim2.$key[$i];
                        $delim2 = ',';
                    }
                    echo '\'';
                    $delim1 = ','."\n";
                }
                echo "\n".'        )';
            }
            if (count($this->mUniqueKeys)>0) {
                echo ','."\n".'        \'unique\' => array('."\n";
                $delim1 = '';
                foreach($this->mUniqueKeys as $name=>$key) {
                    echo $delim1.'            \''.$name.'\' => \'';
                    $delim2 = '';
                    for ($i=0; $i<count($key); $i++) {
                        echo $delim2.$key[$i];
                        $delim2 = ',';
                    }
                    echo '\'';
                    $delim1 = ','."\n";
                }
                echo "\n".'        )';
            }
            if ($userSysField) {
                echo ','."\n".'        \'usesys\' => true';
            }
            echo "\n".'    );'."\n";
            
            echo "</pre>";
        }
    }
}
?>
