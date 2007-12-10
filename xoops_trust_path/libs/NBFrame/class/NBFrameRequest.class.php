<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameRequest')) {
    if (!defined('NBFRAME_NO_DEFAULT_PARAM')) define('NBFRAME_NO_DEFAULT_PARAM', '__nodefault__');
    class NBFrameRequest {
        var $mRequests = array();
        var $mRawRequests = array();
        var $mErrorMsgs = array();
        var $mParams = array();

        function defParam($name, $reqTypes, $valType = '', $defaultValue = NBFRAME_NO_DEFAULT_PARAM, $mustExist = false){
            if (!is_array($reqTypes)) {
                if ($reqTypes) {
                    $para_tmp = $reqTypes;
                    $reqTypes = array();
                    $reqTypes[] = $para_tmp;
                } else {
                    $reqTypes = array('POST','GET');
                }
            }
            $this->mParams[$name]['reqTypes'] = $reqTypes;
            $this->mParams[$name]['valType'] = $valType;
            $this->mParams[$name]['defaultValue'] = $defaultValue;
            $this->mParams[$name]['mustExist'] = $mustExist;
        }
        
        function parseRequest() {
            $this->mErrorMsgs = array();
            foreach($this->mParams as $name => $param) {
                $result = $this->_parseRequest($name, $param);
                if (isset($result['RawRequest'])) {
                    $this->mRawRequests[$name] = $result['RawRequest'];
                }
                if (isset($result['ErrorMsg'])) {
                    $this->mErrorMsgs[] = $result['ErrorMsg'];
                }
                if (isset($result['Request'])) {
                    $this->mRequests[$name] = $result['Request'];
                }
            }
        }
        
        function _parseRequest($name, $param) {
            $result = array();
            $paraFound = false;
            foreach($param['reqTypes'] as $req_type) {
                switch (strtoupper($req_type)) {
                    case 'POST':
                        if (isset($_POST[$name])) {
                            $paraValue = $this->_removeMagicQuotes($_POST[$name]);
                            $paraFound = true;
                        }
                        break;
                    case 'GET':
                        if (isset($_GET[$name])) {
                            $paraValue = $this->_removeMagicQuotes($_GET[$name]);
                            $paraFound = true;
                        }
                        break;
                    case 'COOKIE':
                        if (isset($_COOKIE[$name])) {
                            $paraValue = $this->_removeMagicQuotes($_COOKIE[$name]);
                            $paraFound = true;
                        }
                        break;
                    case 'SESSION':
                        if (isset($_SESSION[$name])) {
                            $paraValue = $_SESSION[$name];
                            $paraFound = true;
                        }
                        break;
                    default:
                }
                if ($paraFound) {
                    $result['RawRequest'] = $paraValue;
                    break;
                }
            }

            if ($param['mustExist'] && !$paraFound) {
                $result['ErrorMsg'] = new NBFrameRequestErr('Required parameter['.$name.'] should not be empty.');
                return $result;
            }

            if (!$paraFound) {
                if ($param['defaultValue'] !== NBFRAME_NO_DEFAULT_PARAM) {
                    $paraValue = $param['defaultValue'];
                } elseif ($type == 'string-yn') {
                    $paraValue = 'N';
                } elseif ($type == 'check-01') {
                    $paraValue = '0';
                }
            }

            if (isset($paraValue)) {
                if (!empty($param['valType'])) {
                    switch( $param['valType']) {
                        case 'html':
                            // do nothing
                            break;
                        case 'string':
                            $paraValue = trim(strip_tags($paraValue));
                            break;
                        case 'string-yn':
                            $paraValue = ($paraValue == 'Y') ? 'Y' : 'N';
                            break;
                        case 'check-01':
                            $paraValue = ($paraValue == '1') ? '1' : '0';
                            break;
                        case 'array-int':
                            settype($paraValue,'array');
                            array_walk($paraValue, array(&$this,'_array_int_callback'));
                            break;
                        default:
                            settype($paraValue, $param['valType']);
                    }
                }
                $result['Request'] = $paraValue;
            }
            return $result;
        }

        function hasError() {
            return (count($this->mErrorMsgs)!=0);
        }
        
        function testParam($name) {
            return (array_key_exists($name, $this->mRequests) && !empty($this->mRequests[$name]));
        }

        function getParam($name) {
            if (isset($this->mRequests[$name])) {
                return $this->mRequests[$name];
            } else {
                return null;
            }
        }
        
        function getRequest($name, $reqTypes, $valType = '', $defaultValue = NBFRAME_NO_DEFAULT_PARAM, $mustExist = false){
            if (!is_array($reqTypes)) {
                if ($reqTypes) {
                    $para_tmp = $reqTypes;
                    $reqTypes = array();
                    $reqTypes[] = $para_tmp;
                } else {
                    $reqTypes = array('POST','GET');
                }
            }
            $param['reqTypes'] = $reqTypes;
            $param['valType'] = $valType;
            $param['defaultValue'] = $defaultValue;
            $param['mustExist'] = $mustExist;
            
            $result = $this->_parseRequest($name, $param);
            if (isset($result['ErrorMsg'])) {
                return $result['ErrorMsg'];
            }
            if (isset($result['Request'])) {
                return $result['Request'];
            }
        }

        function _array_int_callback(& $value) {
            settype($value,'integer');
            return $value;
        }

        function _removeMagicQuotes($mixed) {
            if( get_magic_quotes_gpc()) {
                if(is_array($mixed)) {
                    foreach($mixed as $k => $v) {
                        $mixed[$k] = $this->removeMagicQuotes($v);
                    }
                } else {
                    $mixed = stripslashes($mixed);
                }
            }
            return $mixed;
        }
    }
    class NBFrameRequestErr {
        var $errMsg;
        function NBFrameRequestErr($str) {
            $this->errMsg = $str;
        }
        function get() {
            return $this->errMsg;
        }
    }
}