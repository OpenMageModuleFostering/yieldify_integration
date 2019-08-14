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

		$new_uuid = $this->getRequest()->getParam('new_uuid');
		if(!is_null($new_uuid)) {
			$this->variablesHelper->setVariable($new_uuid);
			$this->_redirect('*/*/index');
		}

		$this->renderLayout();
	}
}
