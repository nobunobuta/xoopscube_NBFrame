<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameCachedObjectHandler')) {
    NBFrame::using('ObjectHandler');

    class NBFrameCachedObjectHandler  extends NBFrameObjectHandler
    {
        var $tableName;
        var $useFullCache;
        var $cacheLimit;
        var $_entityClassName;
        var $_errors;
        var $_fullCached;
        var $_sql;

        function NBFrameObjectHandler($db)
        {
            $this->_entityClassName = preg_replace("/handler$/i","", get_class($this));
            $this->XoopsObjectHandler($db);
            $this->_errors = array();
            $this->useFullCache = true;
            $this->cacheLimit = 0;
            $this->_fullCached = false;
        }

        /**
         * レコードの取得(プライマリーキーによる一意検索）
         *
         * @param   mixed $key 検索キー
         *
         * @return  object  {@link NBFrameObject}, FALSE on fail
         */
        function &get($keys)
        {
            $ret = false;
            $record =& $this->create(false);
            $recordKeys = $record->getKeyFields();
            $recordVars = $record->getVars();
            if (gettype($keys) != 'array') {
                if (count($recordKeys) == 1) {
                    $keys = array($recordKeys[0] => $keys);
                } else {
                    return $ret;
                }
            }
            $whereStr = "";
            $whereAnd = "";
            $cacheKey = array();
            foreach ($record->getKeyFields() as $k => $v) {
                if (array_key_exists($v, $keys)) {
                    $whereStr .= $whereAnd . "`$v` = ";
                    if (($recordVars[$v]['data_type'] == XOBJ_DTYPE_INT) || ($recordVars[$v]['data_type'] == XOBJ_DTYPE_FLOAT)) {
                        $whereStr .= $keys[$v];
                    } else {
                        $whereStr .= $this->db->quoteString($keys[$v]);
                    }
                    $whereAnd = " AND ";
                    $cacheKey[$v] = $keys[$v];
                } else {
                    return $ret;
                }
            }
            $cacheKey = serialize($cacheKey);
            if ($GLOBALS['_NBFrameTableCache']->exists($this->tableName, $cacheKey)) {
                $record->assignVars($GLOBALS['_NBFrameTableCache']->get($this->tableName, $cacheKey));
                return $record;
            }
            $sql = sprintf("SELECT * FROM %s WHERE %s",$this->tableName, $whereStr);

            if ( !$result =& $this->query($sql) ) {
                return $ret;
            }
            $numrows = $this->db->getRowsNum($result);
//      echo $numrows."<br/>";
            if ( $numrows == 1 ) {
                $row = $this->db->fetchArray($result);
                $record->assignVars($row);
                $GLOBALS['_NBFrameTableCache']->set($this->tableName, $cacheKey, $row, $this->cacheLimit);
                $this->db->freeRecordSet($result);
                return $record;
            }
            unset($record);
            return $ret;
        }
        /**
         * レコードの保存
         *
         * @param   object  &$record    {@link NBFrameObject} object
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function insert(&$record,$force=false,$updateOnlyChanged=false)
        {
            if ( get_class($record) != $this->_entityClassName ) {
                return false;
            }
            if ( !$record->isDirty() ) {
                return true;
            }
            if (!$record->cleanVars()) {
                $this->_errors += $record->getErrors();
                return false;
            }
            $vars = $record->getVars();
            $cacheRow = array();
            if ($record->isNew()) {
                $fieldList = "(";
                $valueList = "(";
                $delim = "";
                foreach ($record->cleanVars as $k => $v) {
                    if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
                        continue;
                    }
                    $fieldList .= $delim ."`$k`";
                    if ($record->isAutoIncrement($k)) {
                        $v = $this->getAutoIncrementValue();
                    }
                    if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
                        $value = preg_replace('/^__MySqlFunc__/', '', $v);
                    } elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
                        if (!is_null($v)) {
                            $v = intval($v);
                            $v = ($v) ? $v : 0;
                            $valueList .= $delim . $v;
                        } else {
                            $valueList .= $delim . 'null';
                        }
                    } elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
                        if (!is_null($v)) {
                            $v = (float)($v);
                            $v = ($v) ? $v : 0;
                            $valueList .= $delim . $v;
                        } else {
                            $valueList .= $delim . 'null';
                        }
                    } else {
                        if (!is_null($v)) {
                            $valueList .= $delim . $this->db->quoteString($v);
                        } else {
                            $valueList .= $delim . $this->db->quoteString('');;
                        }
                    }
                    $cacheRow[$k] = $v;
                    $delim = ", ";
                }
                $fieldList .= ")";
                $valueList .= ")";
                $sql = sprintf("INSERT INTO %s %s VALUES %s", $this->tableName,$fieldList,$valueList);
            } else {
                $setList = "";
                $setDelim = "";
                $whereList = "";
                $whereDelim = "";
                foreach ($record->cleanVars as $k => $v) {
                    if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
                        continue;
                    }
                    if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
                        $value = preg_replace('/^__MySqlFunc__/', '', $v);
                    } elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
                        $v = intval($v);
                        $value = ($v) ? $v : 0;
                    } elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
                        $v = (float)($v);
                        $value = ($v) ? $v : 0;
                    } else {
                        $value = $this->db->quoteString($v);
                    }

                    if ($record->isKey($k)) {
                        $whereList .= $whereDelim . "`$k` = ". $value;
                        $whereDelim = " AND ";
                    } else {
                        if ($updateOnlyChanged && !$vars[$k]['changed']) {
                            continue;
                        }
                        $setList .= $setDelim . "`$k` = ". $value . " ";
                        $setDelim = ", ";
                    }
                    $cacheRow[$k] = $v;
                }
                if (!$setList) {
                    $record->resetChenged();
                    return true;
                }
                $sql = sprintf("UPDATE %s SET %s WHERE %s", $this->tableName, $setList, $whereList);
            }
            if (!$result =& $this->query($sql, $force)) {
                return false;
            }
            if ($record->isNew()) {
                $idField=$record->getAutoIncrementField();
                $idValue=$this->db->getInsertId();
                $record->assignVar($idField,$idValue);
                $cacheRow[$idField] = $idValue;
            }
            if (!$updateOnlyChanged) {
                $GLOBALS['_NBFrameTableCache']->set($this->tableName, $record->cacheKey() ,$cacheRow, $this->cacheLimit);
            } else {
                $GLOBALS['_NBFrameTableCache']->reset($this->tableName, $record->cacheKey());
                $this->_fullCached = false;
            }
            $record->resetChenged();
            return true;
        }

        /**
         * レコードの削除
         *
         * @param   object  &$record  {@link NBFrameObject} object
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function delete(&$record,$force=false)
        {
            $GLOBALS['_NBFrameTableCache']->reset($this->tableName, $record->cacheKey());
            return parent::delete($record,$force);
        }

        /**
         * テーブルの条件検索による複数レコード取得
         *
         * @param   object  $criteria   {@link NBFrameObject} 検索条件
         * @param   bool $id_as_key     プライマリーキーを、戻り配列のキーにする場合はtrue
         *
         * @return  mixed Array         検索結果レコードの配列
         */
        function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false)
        {
            $records = array();
            //今のところは、非常に限定された条件でしかキャッシュを使えない
            if (($this->useFullCache) && ($this->_fullCached) && (empty($criteria))&& (!$fieldlist) && (!$distinct) && (!$joindef)) {
                foreach ($GLOBALS['_NBFrameTableCache']->getFull($this->tableName) as $myrow) {
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
                                $r[$myrow[$ids[$i]]] = array();
                                $r =& $r[$myrow[$ids[$i]]];
                            }
                        }
                    }
                    unset($record);
                }
                return $records;
            }

            if ($result =& $this->open($criteria, $fieldlist, $distinct, $joindef)) {
                if (($this->useFullCache) && (empty($criteria)) && (!$fieldlist) && (!$distinct) && (!$joindef)) {
                    $this->_fullCached = true;
                }
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
                    if (!$fieldlist) {
                        $GLOBALS['_NBFrameTableCache']->set($this->tableName, $record->cacheKey(), $myrow, $this->cacheLimit);
                    }
                    unset($record);
                }
                $this->db->freeRecordSet($result);
            }
            return $records;
        }

        function &getNext(&$resultSet, $setCache=true)
        {
            if ($myrow = $this->db->fetchArray($resultSet)) {
                $record =& $this->create(false);
                $record->assignVars($myrow);
                if ($setCache) {
                    $GLOBALS['_NBFrameTableCache']->set($this->tableName, $record->cacheKey(), $myrow, $this->cacheLimit);                }
                return $record;
            } else {
                $result = false;
                return $result;
            }
        }

        /**
         * テーブルの条件検索による複数レコード一括更新(対象フィールドは一つのみ)
         *
         * @param   string  $fieldname  更新フィールド名
         * @param   mixed   $fieldvalue 更新値
         * @param   object  $criteria   {@link NBFrameObject} 検索条件
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  mixed Array         検索結果レコードの配列
         */
        function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
        {
            $GLOBALS['_NBFrameTableCache']->clear($this->tableName);
            $this->_fullCached = false;
            return parent::updateAll($fieldname, $fieldvalue, $criteria, $force);
        }

        /**
         * テーブルの条件検索による複数レコード削除
         *
         * @param   object  $criteria   {@link NBFrameObject} 検索条件
         * @param   bool    $force      POSTメソッド以外で強制更新する場合はture
         *
         * @return  bool    成功の時は TRUE
         */
        function deleteAll($criteria = null, $force=false)
        {
            $GLOBALS['_NBFrameTableCache']->clear($this->tableName);
            $this->_fullCached = false;
            return parent::deleteAll($criteria, $force);
        }

        function getAutoIncrementValue()
        {
            return $this->db->genId(get_class($this).'_id_seq');
        }
    }
}
?>
