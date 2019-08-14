<?php
class Yieldify_Integration_Helper_Data extends Mage_Core_Helper_Abstract {
	const VARIABLE_NAME = 'yieldify_uuid';
	const ENVIRONMENT = 'YIELDIFY_ENV';
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

	private function _tryCreateVariable() {
		$tmp = $this->_getVariable();
		if(!is_null($tmp) && $tmp!=='')
			return false;
		try {
			$this->_createVariable();
			return true;
		}
		catch (Exception $e) {
			return false;
		}
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
				->setHtmlValue('')
				->setPlainValue('')
				->save();
	}
}
