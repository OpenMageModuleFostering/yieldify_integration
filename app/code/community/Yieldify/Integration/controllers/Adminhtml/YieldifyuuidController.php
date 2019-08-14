<?php

class Yieldify_Integration_Adminhtml_YieldifyuuidController extends Mage_Adminhtml_Controller_Action {

	private $variablesHelper;

	public function __construct(
		Zend_Controller_Request_Abstract $request,
		Zend_Controller_Response_Abstract $response,
		array $invokeArgs = array()) {

		$this->variablesHelper = Mage::helper('yieldify_helper');
		parent::__construct($request, $response, $invokeArgs);
	}

	public function indexAction() {
		$this->loadLayout()
			->_setActiveMenu('yieldifytab')
			->_title($this->__('Yieldify - Set Yieldify ID'));

		// make sure the variable is valid by forcing it to check while getting it
		$variable = $this->variablesHelper->getVariable();

		$new_uuid = $this->getRequest()->getParam('new_uuid');
		if(!is_null($new_uuid)) {
			$error_type = $this->variablesHelper->validateID($new_uuid);
			if($error_type==='none') {
				$this->variablesHelper->setVariable($new_uuid);
			}
			Mage::getSingleton('adminhtml/session')->setYieldifyError($error_type);
			$this->_redirect('*/*/index');
			return;
		}

		$error = Mage::getSingleton('adminhtml/session')->getYieldifyError(true);
		$last_valid_id = Mage::getSingleton('adminhtml/session')->getYieldifyLastValidId(true);
		if(!is_null($error))
			Mage::register('yieldify_error', $error, true);
		if(!is_null($last_valid_id))
			Mage::register('yieldify_last_valid_id', $last_valid_id, true);

		$this->renderLayout();
	}

	public function clearAction() {
		$variable = $this->variablesHelper->getVariable();

		$this->variablesHelper->setVariable('');
		Mage::getSingleton('adminhtml/session')->setYieldifyError('clear');
		Mage::getSingleton('adminhtml/session')->setYieldifyLastValidId($variable);

		$this->_redirect('*/*/index');
		return;
	}
}
