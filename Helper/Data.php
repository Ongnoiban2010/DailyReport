<?php 
namespace Dtn\DailyReport\Helper; 

use Magento\Framework\App\Helper\AbstractHelper; 
use Magento\Framework\App\Helper\Context; 
use Magento\Framework\App\Config\ScopeConfigInterface; 
use Magento\Framework\Encryption\EncryptorInterface; 

class Data extends AbstractHelper { 
	
	protected $encryptor; 
	
	public function __construct( 
		Context $context, EncryptorInterface $encryptor ) 
	{ 
		parent::__construct($context); 
		$this->encryptor = $encryptor; 
	} 

	/* * @return bool */ 
	public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->isSetFlag('dailyreport/general/enable', $scope); 
	} 

	/* * @return string */ 
	public function getEmail($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/email', $scope); 
	} 
	// public function getDate($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	// { 
	// 	return $this->scopeConfig->getValue( 'dailyreport/general/date', $scope ); 
	// } 
	
	public function getFrequency($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/frequency', $scope); 
	} 

	public function getTime($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/time', $scope); 
	} 

	public function getSsl($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/ssl', $scope); 
	} 

	public function getSmtpHost($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/smtphost', $scope); 
	}

	public function getSmtpPost($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/smtpport', $scope); 
	} 

	public function getAuth($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/auth', $scope); 
	}

	public function getUsername($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/username', $scope); 
	} 

	public function getPassword($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT) 
	{ 
		return $this->scopeConfig->getValue('dailyreport/general/password', $scope); 
	} 

	public function getCcEmail($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
	{
		return $this->scopeConfig->getValue('dailyreport/general/ccemail', $scope);
	}
}