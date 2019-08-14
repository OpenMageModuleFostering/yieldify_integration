<?php
class Yieldify_Integration_Helper_Data extends Mage_Core_Helper_Abstract {
	const VARIABLE_NAME = 'yieldify_uuid';
	const ENVIRONMENT = 'YIELDIFY_ENV';
	const ID_REGEX = '/^[\d,\w]{8}-[\d,\w]{4}-[\d,\w]{4}-[\d,\w]{4}-[\d,\w]{12}$/';
	protected static $DOMAIN_LIST = array( // due to PHP5.5 not allowing arrays to be constant variables
		'production'  => 'app.yieldify.com',
	);

	private $currentVersion;
	private $majorVersion;
	private $minorVersion;
	private $valueName;

	public function __construct() {
		$this->currentVersion = explode('.',Mage::getVersion());
		$this->majorVersion = intval($this->currentVersion[0]);
		$this->minorVersion = intval($this->currentVersion[1]);
		$this->valueName = ($this->majorVersion==1 && $this->minorVersion>=6)? 'plain' : 'text';
	}

	public function validateID($new_uuid) {
		// check if invalid format on id
		if(!$this->_preg_match($new_uuid))
			return 'format';

		// check if uuid is invalid by checking the tag
		$error_type = 'none';
		try {
			$contents = file_get_contents('http://'.$this->getDomain().'/yieldify/'.
				'code.js?w_uuid='.$new_uuid.
				'&i_s=m&loca=http://'.$_SERVER['HTTP_HOST'].'/'
			);
			if( is_null($http_response_header) ||
					!is_array($http_response_header) ||
					count($http_response_header)<5 ||
					$http_response_header[4]!=='Status: 200 OK')
				$error_type = 'server';
			else if(strlen($contents)===0)
				$error_type = 'invalid';
		}
		catch(Exception $e) {
			$error_type = 'server';//'unknown';
		}
		return $error_type;
	}

	public function getVariable() {
		$this->_tryCreateVariable();
		$tmp = $this->_getVariable();
		if(!is_null($tmp) && $tmp!=='')
			return $tmp;
		else
			return '';
	}

	public function setVariable($uuid) {
		$this->_tryCreateVariable();
		if(!$this->_preg_match($uuid) && $uuid!=='')
			return false;
		return $this->_setVariable($uuid);
	}

	public function getDomain() {
		$env_var = getenv(self::ENVIRONMENT);
		$keys = array_keys(self::$DOMAIN_LIST);

		if(count($keys)===1 || $env_var===false) {
			return self::$DOMAIN_LIST[$keys[0]];
		}

		$env_var = strtolower($env_var);
		foreach($keys as $key) {
			if(strpos($key, $env_var)===0)
				return self::$DOMAIN_LIST[$key];
		}

		return self::$DOMAIN_LIST[$keys[0]];
	}

	public function getIDRegex() {
		return self::ID_REGEX;
	}

	private function _tryCreateVariable() {
		try {
			$this->_createVariable();
			return true;
		}
		catch (Exception $e) {
			$tmp = $this->_getVariable();
			//check if valid(1) or invalid(0)
			if(!$this->_preg_match($tmp))
				$this->_setVariable('');
			return false;
		}
	}

	private function _preg_match($uuid) {
		if(preg_match(self::ID_REGEX, $uuid)!==1)
			return false;
		return true;
	}

	private function _getVariable() {
		return Mage::getModel('core/variable')
			->loadByCode(self::VARIABLE_NAME)
			->getValue($this->valueName);
	}

	private function _setVariable($uuid) {
		return Mage::getModel('core/variable')
			->loadByCode(self::VARIABLE_NAME)
			->setHtmlValue($uuid)
			->setPlainValue($uuid)
			->save();
	}

	private function _createVariable() {
		return Mage::getModel('core/variable')
			->setCode(self::VARIABLE_NAME)
			->setName(self::VARIABLE_NAME)
			->save();
	}
}
