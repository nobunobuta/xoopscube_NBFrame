<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectHandler')) {
    require_once XOOPS_ROOT_PATH.'/kernel/object.php';

    class NBFrameObjectHandler  extends XoopsObjectHandler
    {
        var $mTableName = null;
        var $mTableBaseName = null;
        var $mTableAlias = null;
        var $mClassName;
        var $mEntityClassName;
        var $mErrors;
        var $mSql;
        var $mEnvironment = null;
        var $mUseModuleTablePrefix = true;
        var $mLanguage;
        var $mObjectCache = null;
        var $mLogQueryError = true;

        /**
         * Enter description here...
         *
         * @param XoopsDB $db
         * @return NBFrameObjectHandler
         */
        function NBFrameObjectHandler($db) {
            $this->mClassName = get_class($this);
            $this->mEntityClassName = preg_replace("/handler$/i","", $this->mClassName);
            parent::XoopsObjectHandler($db);
            if ($this->mTableBaseName) {
                $this->mTableName = $this->db->prefix($this->mTableBaseName);
            } else {
                if ($this->mTableName) {
                    $this->mTableBaseName = $this->mTableName;
                    $this->mTableName = $this->db->prefix($this->mTableName);
                }
            }
            $this->mErrors = array();
        }

        /**
         * Get a base name of table
         *
         * @return string
         */
        function getTableBaseName() {
            return $this->mTableBaseName;
        }

        /**
         * Set a base name of table
         *
         * @param string $name
         */
        function setTableBaseName($name) {
            if ($name) {
                $this->mTableBaseName = $name;
                $this->mTableName = $this->db->prefix($name);
            }
        }

        function getErrors($html=true, $clear=true) {
            $error_str = "";
            $delim = $html ? "<br />\n" : "\n";
            if (count($this->mErrors)) {
                $error_str = implode($delim, $this->mErrors);
            }
            if ($clear) {
                $this->mErrors = array();
            }
            return $error_str;
        }
        
        function hasError() {
            return (count($this->mErrors)) ? true : false;
        }
        
        function setError($error_str) {
            $this->mErrors[] = $error_str;
        }

        function setAlias($alias) {
            $this->mTableAlias = $alias;
        }

        function getAlias() {
            return($this->mTableAlias);
        }

        /**
         * レコードオブジェクトの生成
         *
         * @param   boolean $isNew 新規レコード設定フラグ
         *
         * @return  NBFrameObject
         */
        function &create($isNew = true) {
            if (class_exists($this->mEntityClassName)) {
                $record =& new $this->mEntityClassName;
            } else {
                $record =& new NBFrameObject;
            }
            if (!$record->varsDefined()) {
                if (empty($this->mObjectCache)) {
                    NBFrame::using('TebleParser');
                    $parser = new NBFrameTebleParser($this->db);
                    $parser->setInitVars($this->mTableName, $record);
                    $record->setAttribute('dohtml', 0);
                    $record->setAttribute('doxcode', 1);
                    $record->setAttribute('dosmiley', 1);
                    $record->setAttribute('doimage', 1);
                    $record->setAttribute('dobr', 1);
                    $this->mObjectCache=serialize($record);
                } else {
                    unset($record);
                    $record = unserialize($this->mObjectCache);
                }
            }
            $record->mClassName = $this->mEntityClassName;
            $record->prepare();
            if ($isNew) {
                $record->setNew();
            }
            $record->mHandler =& $this;
            return $record;
        }

        /**
         * レコードの取得(プライマリーキーによる一意検索）
         *
         * @param   mixed $key 検索キー
         *
         * @return  NBFrameObject FALSE on fail
         */
        function &get($keys) {
            $ret = false;
            $record =& $this->create(false);

            if ($whereStr = $this->_key2where($keys)) {
                $sql = sprintf("SELECT * FROM %s WHERE %s",$this->mTableName, $whereStr);

                if ( !$result =& $this->query($sql) ) {
                    return $ret;
                }

                $numrows = $this->db->getRowsNum($result);
                if ( $numrows == 1 ) {
                    $row = $this->db->fetchArray($result);
                    $record->assignVars($row);
                    $this->db->freeRecordSet($result);
                    return $record;
                }
            }
            unset($record);
            return $ret;
        }


        function _key2where($keys) {
            $record =& $this->create(false);
            $recordKeys = $record->getKeyFields();
            $recordVars = $record->getVars();
            if (!is_array($keys)) {
                if (count($recordKeys) == 1) {
                    $keys = array($recordKeys[0] => $keys);
                } else {
                    return false;
                }
            }
            $whereStr = "";
            $whereAnd = "";
            foreach ($recordKeys as $key) {
                if (array_key_exists($key, $keys)) {
                    $whereStr .= $whereAnd . "`$key` = ";
                    if ($recordVars[$key]['data_type'] == XOBJ_DTYPE_INT) {
                        $whereStr .= intval($keys[$key]);
                    } else if ($recordVars[$key]['data_type'] == XOBJ_DTYPE_FLOAT) {
                        $whereStr .= floatval($keys[$key]);
                    } else {
                        $whereStr .= $this->db->quoteString($keys[$key]);
                    }
                    $whereAnd = " AND ";
                } else {
                    unset($record);
                    return false;
                }
            }
            unset($record);
            return $whereStr;
        }

        /**
         * レコードの保存
         *
         * @param   NBFrameObject  $record
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function insert(&$record,$force=false,$updateOnlyChanged=false) {
            if (is_a($record, 'NBFrameObject') && $record->mClassName != $this->mEntityClassName ) {
                return false;
            }
            if ( !$record->isDirty() ) {
                return true;
            }
            if (!$record->cleanVars()) {
                $this->mErrors += $record->getErrors();
                return false;
            }
            $vars = $record->getVars();
            if ($record->isNew()) {
                $fieldList = "(";
                $valueList = "(";
                $delim = "";
                foreach ($record->cleanVars as $field => $value) {
                    if ($vars[$field]['var_class'] != XOBJ_VCLASS_TFIELD) {
                        continue;
                    }
                    $fieldList .= $delim ."`$field`";
                    if ($record->isAutoIncrement($field)) {
                        $value = $this->getAutoIncrementValue();
                    }
                    if (isset($vars[$field]['func'])) {  // for value using MySQL function.
                        $value = $vars[$field]['func'].'('.$value.')';
                    } elseif ($vars[$field]['data_type'] == XOBJ_DTYPE_INT) {
                        if (!is_null($value)) {
                            $value = intval($value);
                            $value = ($value) ? $value : 0;
                            $valueList .= $delim . $value;
                        } else {
                            $valueList .= $delim . 'null';
                        }
                    } elseif ($vars[$field]['data_type'] == XOBJ_DTYPE_FLOAT) {
                        if (!is_null($value)) {
                            $value = (float)($value);
                            $value = ($value) ? $value : 0;
                            $valueList .= $delim . $value;
                        } else {
                            $valueList .= $delim . 'null';
                        }
                    } else {
                        if (!is_null($value)) {
                            $valueList .= $delim . $this->db->quoteString($value);
                        } else {
                            $valueList .= $delim . $this->db->quoteString('');
                        }
                    }
                    $delim = ", ";
                }
                if ($record->mUseSystemField == true) {
                    if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
                        $uid = intval($GLOBALS['xoopsUser']->getVar('uid'));
                    } else {
                        $uid = 0;
                    }
                    $fieldList .= $delim ."`_NBsys_create_user`";
                    $valueList .= $delim . $uid;
                    $delim = ", ";
                    $fieldList .= $delim ."`_NBsys_update_user`";
                    $valueList .= $delim . $uid;
                    $fieldList .= $delim ."`_NBsys_create_time`";
                    $valueList .= $delim . 'NOW()';
                    $fieldList .= $delim ."`_NBsys_update_time`";
                    $valueList .= $delim . 'NOW()';
                }
                $fieldList .= ")";
                $valueList .= ")";
                $sql = sprintf("INSERT INTO %s %s VALUES %s", $this->mTableName, $fieldList, $valueList);
            } else {
                $setList = "";
                $setDelim = "";
                $whereList = "";
                $whereDelim = "";
                foreach ($record->cleanVars as $field => $value) {
                    if ($vars[$field]['var_class'] != XOBJ_VCLASS_TFIELD) {
                        continue;
                    }
                    if (isset($vars[$field]['func'])) {  // for value using MySQL function.
                        $value = $vars[$field]['func'].'('.$value.')';
                    } elseif ($vars[$field]['data_type'] == XOBJ_DTYPE_INT) {
                        $value = intval($value);
                        $value = ($value) ? $value : 0;
                    } elseif ($vars[$field]['data_type'] == XOBJ_DTYPE_FLOAT) {
                        $value = (float)($value);
                        $value = ($value) ? $value : 0;
                    } else {
                        $value = $this->db->quoteString($value);
                    }

                    if ($record->isKey($field)) {
                        $whereList .= $whereDelim . "`$field` = ". $value;
                        $whereDelim = " AND ";
                    } else {
                        if ($updateOnlyChanged && !$vars[$field]['changed']) {
                            continue;
                        }
                        $setList .= $setDelim . "`$field` = ". $value . " ";
                        $setDelim = ", ";
                    }
                }
                if ($record->mUseSystemField == true) {
                    if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
                        $uid = intval($GLOBALS['xoopsUser']->getVar('uid'));
                    } else {
                        $uid = 0;
                    }
                    $setList .= $setDelim ."`_NBsys_update_user`=$uid";
                    $setList .= $setDelim ."`_NBsys_update_time`=NOW()";
                    $setList .= $setDelim ."`_NBsys_update_count`=`_NBsys_update_count`+1";
                    $setDelim = ", ";
                    foreach ($record->mVerifier as $key=>$value) {
                        $whereList .= $whereDelim . '`'.$key.'` = '. $record->cleanVars[$value];
                    }
                }
                if (!$setList) {
                    $record->resetChenged();
                    return true;
                }
                $sql = sprintf("UPDATE %s SET %s WHERE %s", $this->mTableName, $setList, $whereList);
            }
            if (!$result =& $this->query($sql, $force)) {
                return false;
            }
            if ($this->db->getAffectedRows() == 0) {
                if (!$record->isNew()) {
                    $this->setError($this->__e('This record may have been updated by somebody'));
                }
                return false;
            }
            if ($record->isNew()) {
                $idField=$record->getAutoIncrementField();
                $idValue=$this->db->getInsertId();
                $record->assignVar($idField,$idValue);
            }
            $record->resetChenged();
            return true;
        }

        function updateByField(&$record, $fieldName, $fieldValue, $not_gpc=false) {
            $record->setVar($fieldName, $fieldValue, $not_gpc);
            return $this->insert($record, true, true);
        }

        /**
         * レコードの削除
         *
         * @param   NBFrameObject  $record
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function delete(&$record, $force=false) {
            if (is_a($record, 'NBFrameObject') && $record->mClassName != $this->mEntityClassName ) {
                return false;
            }
            if (!$record->cleanVars()) {
                $this->mErrors[] = $this->db->error();
                return false;
            }
            $vars = $record->getVars();
            $whereList = "";
            $whereDelim = "";
            foreach ($record->cleanVars as $field => $value) {
                if ($record->isKey($field)) {
                    if (($vars[$field]['data_type'] == XOBJ_DTYPE_INT)||($vars[$field]['data_type'] == XOBJ_DTYPE_FLOAT)) {
                        $value = $value;
                    } else {
                        $value = $this->db->quoteString($value);
                    }
                    $whereList .= $whereDelim . "`$field` = ". $value;
                    $whereDelim = " AND ";
                }
            }
            $sql = sprintf("DELETE FROM %s WHERE %s", $this->mTableName, $whereList);
            if (!$result =& $this->query($sql, $force)) {
                return false;
            }
            return true;
        }

        /**
         * テーブルの条件検索による複数レコード取得
         *
         * @param   Criteria  $criteria  検索条件
         * @param   bool $id_as_key     プライマリーキーを、戻り配列のキーにする場合はtrue
         *
         * @return  mixed Array         検索結果レコードの配列
         */
        function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false, $having="")
        {
            $records = array();

            if ($result =& $this->open($criteria, $fieldlist, $distinct, $joindef, $having)) {
                while ($myrow = $this->db->fetchArray($result)) {
                    $record =& $this->create(false);
                    $record->assignVars($myrow);
                    if (!$id_as_key) {
                        $records[] =& $record;
                    } else {
                        $ids = $record->getKeyFields();
                        $r =& $records;
                        $count_ids = count($ids);
                        for ($i=0; $i<$count_ids; $i++) {
                            if ($i == $count_ids-1) {
                                $r[$myrow[$ids[$i]]] =& $record;
                            } else {
                                if (!isset($r[$myrow[$ids[$i]]])) {
                                    $r[$myrow[$ids[$i]]] = array();
                                }
                                $r =& $r[$myrow[$ids[$i]]];
                            }
                        }
                    }
                    unset($record);
                }
                $this->db->freeRecordSet($result);
            }
            return $records;
        }

        /**
         * テーブルの条件検索による複数レコード取得用のOpen （一度には取得しない）
         *
         * @param   Criteria  $criteria  検索条件
         *
         * @return  mixed Array         検索結果レコードの配列
         */
        function &open($criteria = null, $fieldlist="", $distinct = false, $joindef = false, $having="")
        {
            $limit = $start = 0;
            $whereStr = '';
            $orderStr = '';
            if ($distinct) {
                $distinct = "DISTINCT ";
            } else {
                $distinct = "";
            }
            if ($fieldlist) {
                if ($this->getAlias() != '') {
                    $sql = 'SELECT '.$distinct.$fieldlist.' FROM '.$this->mTableName.' AS '.$this->getAlias();
                } else {
                    $sql = 'SELECT '.$distinct.$fieldlist.' FROM '.$this->mTableName;
                }
            } else {
                if ($this->getAlias() != '') {
                    $sql = 'SELECT '.$distinct.'* FROM '.$this->mTableName.' AS '.$this->getAlias();
                } else {
                    $sql = 'SELECT '.$distinct.'* FROM '.$this->mTableName;
                }
            }
            if ($joindef) {
                if ($this->getAlias() != '') {
                    $sql .= $joindef->render($this->getAlias());
                } else {
                    $sql .= $joindef->render($this->mTableName);
                }
            }
            if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
                $whereStr = $this->_renderWhere($criteria);
                $sql .= ' '.$whereStr;
            }
            if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement')||get_class($criteria)=='criteriaelement')) {
                if ($criteria->getGroupby() != ' GROUP BY ') {
                    $sql .= ' '.$criteria->getGroupby();
                    if(strlen($having) > 0){
                        $sql .= ' HAVING '.$having;
                    }
                }
                if ((is_array($criteria->getSort()) && count($criteria->getSort()) > 0)) {
                    $orderStr = 'ORDER BY ';
                    $orderDelim = "";
                    $sortVars = $criteria->getSort();
                    foreach ($sortVars as $sortVar) {
                        if (!is_array($sortVar)) {
                            $orderStr .= $orderDelim . $sortVar.' '.$criteria->getOrder();
                        } else {
                            $orderStr .= $orderDelim . $sortVar['sort'].' '.$sortVar['order'];
                        }
                        $orderDelim = ",";
                    }
                    $sql .= ' '.$orderStr;
                } elseif ($criteria->getSort() != '') {
                    $orderStr = 'ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
                    $sql .= ' '.$orderStr;
                }
                $limit = $criteria->getLimit();
                $start = $criteria->getStart();
            }
            $resultSet =& $this->query($sql, false ,$limit, $start);
            return $resultSet;
        }

        function &getNext(&$resultSet)
        {
            if ($myrow = $this->db->fetchArray($resultSet)) {
                $record =& $this->create(false);
                $record->assignVars($myrow);
                return $record;
            } else {
                $result = false;
                return $result;
            }
        }

        /**
         * テーブルの条件検索による対象レコード件数
         *
         * @param   Criteria  $criteria     検索条件
         *
         * @return  integer                 検索結果レコードの件数
         */
        function getCount($criteria = null)
        {
            $sql = 'SELECT COUNT(*) FROM '.$this->mTableName;
            if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
                $sql .= ' '.$this->_renderWhere($criteria);
            }
            $result =& $this->query($sql);
            if (!$result) {
                return 0;
            }
            list($count) = $this->db->fetchRow($result);
            return $count;
        }

        /**
         * テーブルの条件検索による複数レコード一括更新(対象フィールドは一つのみ)
         *
         * @param   string  $fieldname  更新フィールド名
         * @param   mixed   $fieldvalue 更新値
         * @param   Criteria  $criteria 検索条件
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  mixed Array         検索結果レコードの配列
         */
        function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
        {
            $record = $this->create();
            if ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_INT) {
                $fieldvalue = intval($fieldvalue);
                $fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
            } elseif ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_FLOAT) {
                $fieldvalue = (float)($fieldvalue);
                $fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
            } else {
                $fieldvalue = $this->db->quoteString($fieldvalue);
            }
            $set_clause = $fieldname.' = '.$fieldvalue;
            $sql = 'UPDATE '.$this->mTableName.' SET '.$set_clause;
            if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
                $sql .= ' '.$this->_renderWhere($criteria);
            }
            if (!$result =& $this->query($sql, $force)) {
                return false;
            }
            return true;
        }

        /**
         * テーブルの条件検索による複数レコード削除
         *
         * @param   Criteria $criteria  検索条件
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function deleteAll($criteria = null, $force=false)
        {
            $sql = 'DELETE FROM '.$this->mTableName;
            if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
                $sql .= ' '.$this->_renderWhere($criteria);
            }
            if (!$result =& $this->query($sql, $force)) {
                return false;
            }
            return true;
        }

        function getName($keys, $format='s'){
            if ($GLOBALS['_NBFrameTableCache']->exists($this->mTableName.'__getName_'.$format, serialize($keys))) {
                return $GLOBALS['_NBFrameTableCache']->get($this->mTableName.'__getName_'.$format, serialize($keys));
            }
            if ($record =& $this->get($keys)) {
                $value = $record->getName($format);
                $GLOBALS['_NBFrameTableCache']->set($this->mTableName.'__getName_'.$format, serialize($keys), $value);
                return $value;
            } else {
                return false;
            }
        }

        function &getSelectOptionArray($criteria=null, $gperm_mode='') {
            $resultSet =& $this->open($criteria);
            $optionArray = array();
            while($object =& $this->getNext($resultSet)) {
                if (!empty($gperm_mode) && !$object->checkGroupPerm($gperm_mode)) {
                    continue;
                }
                $optionArray[$object->getKey()] = $object->getName();
            }
            return $optionArray;
        }

        function getAutoIncrementValue()
        {
            return $this->db->genId($this->mClassName.'_id_seq');
        }

        function &query($sql, $force=false, $limit=0, $start=0) {
            static $_xoopsTableQueryCount = 0;
            $_xoopsTableQueryCount++;

            if (!empty($GLOBALS['wpdb'])) {
                $GLOBALS['wpdb']->querycount++;
            }
            if ($force) {
                $result =& $this->db->queryF($sql, $limit, $start);
            } else {
                $result =& $this->db->query($sql, $limit, $start);
            }
            $this->mSql = $sql;
            $this->_start = $start;
            $this->_limit = $limit;

            if (!$result) {
                if ($this->mLogQueryError) {
                    $this->mErrors[] = $this->db->error();
                }
                $result = false;
            }
            return $result;
        }

        function getLastSQL()
        {
            return $this->mSql;
        }

        function _renderWhere($criteria) {
            $whereStr = $this->_makeCriteria4sql($criteria);
            if ($whereStr) return 'WHERE '.$whereStr;
            return '';
        }

        function _makeCriteria4sql($criteria)
        {
            $dmmyObj =& $this->create();
            return $this->_makeCriteriaElement4sql($criteria, $dmmyObj);
        }

        /**
         * @param $criteria CriteriaElement
         * @param $obj NBFrameObject
         */
        function _makeCriteriaElement4sql($criteria, &$obj)
        {
            if (is_a($criteria, "CriteriaElement")) {
                if (is_a($criteria, "CriteriaCompo")) {
                    $queryString = "";
                    $maxCount = count($criteria->criteriaElements);
                    for ($i = 0; $i < $maxCount ; $i++) {
                        $subQueryString = $this->_makeCriteria4sql($criteria->criteriaElements[$i]);
                        if ($subQueryString) {
                            if ($i != 0) {
                                $queryString .= " " . $criteria->conditions[$i];
                            }
                            $queryString .= " " . $subQueryString;
                        }
                    }
                    if ($queryString) {
                        return "(" . $queryString . ")";
                    } else {
                        return null;
                    }
                } else {
                    //
                    // Render
                    //
                    if (method_exists($criteria, 'getName')) {
                        $name = $criteria->getName();
                        $value = $criteria->getValue();
                        $operator = $criteria->getOperator();
                    } else {
                        $name = $criteria->column;
                        $value = $criteria->value;
                        $operator = $criteria->operator;
                    }
                    if ($name != null) {
                        if (isset($obj->vars[$name])) {
                            $type = $obj->vars[$name]['data_type'];
                        } else if (is_array($value) && array_key_exists('type',$value)) {
                            $type = $value['type'];
                            $value = $value['value'];
                        } else {
                            $type = XOBJ_DTYPE_TXTBOX;
                        }
                        if (!in_array(strtoupper($operator), array('IN', 'NOT IN'))) {
                            $value = $this->_makeCriteriaValue4sql($value, $type);
                        } else {
                            if (!is_array($value) && preg_match('/^\(([^)]*)\)$/', trim($value), $match)) {
                                $value = $this->_parseInCause($match[1]);
                            }
                            if (is_array($value)) {
                                foreach (array_keys($value) as $key) {
                                    $value[$key] = $this->_makeCriteriaValue4sql($value[$key], $type);
                                }
                                $value = '('.implode(',', $value).')';
                            } else {
                                return null;
                            }
                        }
                        return $name . " " . $operator . " " . $value;
                    } else {
                        return null;
                    }
                }
            }
        }

        function _makeCriteriaValue4sql($value, $type) {
            switch ($type) {
                case XOBJ_DTYPE_BOOL:
                    $value = $value ? "1" : "0";
                    break;

                case XOBJ_DTYPE_INT:
                case XOBJ_DTYPE_STIME:
                case XOBJ_DTYPE_MTIME:
                case XOBJ_DTYPE_LTIME:
                    $value = intval($value);
                    break;

                case XOBJ_DTYPE_FLOAT:
                    $value = floatval($value);
                    break;

                case XOBJ_DTYPE_TXTBOX:
                case XOBJ_DTYPE_TXTAREA:
                case XOBJ_DTYPE_URL:
                case XOBJ_DTYPE_EMAIL:
                case XOBJ_DTYPE_SOURCE:
                case XOBJ_DTYPE_OTHER:
                    $value = $this->db->quoteString($value);
                    break;
                default:
            }
            return $value;
        }

        function _parseInCause($str) {
           $result = Array();
           $ptr = 0;
           $len = strlen($str);
           while ($ptr < $len) {
               while (($ptr < $len) && (strpos(" \r\t\n",$str[$ptr]) !== false)) $ptr++;
               if ($str[$ptr] == '"') {
                   $ptr++;
                   $q = $ptr;
                   while (($ptr < $len) && ($str[$ptr] != '"')) {
                       if ($str[$ptr] == '\\') { $ptr+=2; continue; }
                       $ptr++;
                   }
                   $result[] = stripslashes(substr($str, $q, $ptr-$q));
                   $ptr++;
                   while (($ptr < $len) && (strpos(" \r\t\n",$str[$ptr]) !== false)) $ptr++;
                   $ptr++;
               } else if ($str[$ptr] == "'") {
                   $ptr++;
                   $q = $ptr;
                   while (($ptr < $len) && ($str[$ptr] != "'")) {
                       if ($str[$ptr] == '\\') { $ptr+=2; continue; }
                       $ptr++;
                   }
                   $result[] = stripslashes(substr($str, $q, $ptr-$q));
                   $ptr++;
                   while (($ptr < $len) && (strpos(" \r\t\n",$str[$ptr]) !== false)) $ptr++;
                   $ptr++;
               } else {
                   $q = $ptr;
                   while (($ptr < $len) && (strpos(",;",$str[$ptr]) === false)) {
                       $ptr++;
                   }
                   $result[] = stripslashes(trim(substr($str, $q, $ptr-$q)));
                   while (($ptr < $len) && (strpos(" \r\t\n",$str[$ptr]) !== false)) $ptr++;
                   $ptr++;
               }
           }
           return $result;
        }

        function __l($msg) {
            $args = func_get_args();
            return $this->mLanguage->__l($msg, $this->mLanguage->_getParams($args));
        }

        function __e($msg) {
            $args = func_get_args();
            return $this->mLanguage->__e($msg, $this->mLanguage->_getParams($args));
        }
    }

    class NBFrameJoinCriteria
    {
        var $_table_name;
        var $_main_field;
        var $_sub_field;
        var $_join_type;
        var $_next_join;
        var $_table_alias; // thanks towdash

        function NBFrameJoinCriteria($table_name, $main_field, $sub_field, $join_type='LEFT', $table_alias="")
        {
            $this->_table_name = $table_name;
            $this->_main_field = $main_field;
            $this->_sub_field = $sub_field;
            $this->_join_type = $join_type;
            $this->_next_join = false;
            $this->_table_alias = $table_alias;
        }

        function cascade(&$joinCriteria) {
            $this->_next_join =& $joinCriteria;
        }

        function render($main_table)
        {
            if($this->_table_alias == ""){
                $table_alias = $this->_table_name;
                $alias_def = "";
            } else {
                $table_alias = $this->_table_alias;
                $alias_def = " AS ".$table_alias;
            }
            $join_str = " ".$this->_join_type." JOIN ".$this->_table_name . $alias_def." ON ".$main_table.".".$this->_main_field."=".$table_alias.".".$this->_sub_field." ";
            if ($this->_next_join) {
                $join_str .= $this->_next_join->render($table_alias);
            }
            return $join_str;
        }

        function getMainAlias() {
            return $this->_main_alias;
        }
    }

    class NBFrameTableCache
    {
        var $cache;

        function set($table, $key, $row, $limit=0) {
            $this->cache[$table][$key] = $row;
            $cache_size = count($this->cache[$table]);
            if (($limit != 0) && $cache_size >$limit) {
                array_splice($this->cache[$table],1, $cache_size-$limit);
            }
        }
        function reset($table, $key) {
            unset($this->cache[$table][$key]);
        }
        function exists($table, $key) {
            return (!empty($this->cache[$table][$key]));
        }
        function &get($table,$key) {
            return $this->cache[$table][$key];
        }
        function &getFull($table) {
            return $this->cache[$table];
        }
        function clear($table) {
            $this->cache[$table] = array();
        }
    }
    $GLOBALS['_NBFrameTableCache'] = new NBFrameTableCache;
}
if (!function_exists('intNBCriteriaVal')) {
    function intNBCriteriaVal($value) {
        return array('value'=>$value, 'type'=>XOBJ_DTYPE_INT, 0);
    }
}
if (!function_exists('strNBCriteriaVal')) {
    function strNBCriteriaVal($value) {
        return array('value'=>$value, 'type'=>XOBJ_DTYPE_TXTBOX, 0);
    }
}
if (!function_exists('floatNBCriteriaVal')) {
    function floatNBCriteriaVal($value) {
        return array('value'=>$value, 'type'=>XOBJ_DTYPE_FLOAT, 0);
    }
}
?>
