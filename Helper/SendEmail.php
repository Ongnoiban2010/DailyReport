<?php

namespace Dtn\DailyReport\Helper;

use Dtn\DailyReport\Helper\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Zend_Mail;
use Zend_Mail_Transport_Smtp;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Dtn\DailyReport\Model\Mail\EmailTemplate;

class SendEmail extends AbstractHelper
{	
	protected $encryptor;

	protected $helper;

	protected $_date;

	protected $emailTemplate;

	function __construct(
		Context $context,
		Data $helper,
		EncryptorInterface $encryptor,
		TimezoneInterface $date,
		EmailTemplate $emailTemplate
	) {
		parent::__construct($context);
		$this->encryptor = $encryptor;
		$this->helper = $helper;
		$this->_date =  $date;
		$this->emailTemplate = $emailTemplate;
	}

	public function getEmail() 
	{ 
		return $this->helper->getEmail(); 
	}

	public function isEnabled() 
	{ 
		return $this->helper->isEnabled();
	} 

	public function getSsl() 
	{ 
		return $this->helper->getSsl();
	} 

	public function getSmtpHost() 
	{ 
		return $this->helper->getSmtpHost();
	}

	public function getSmtpPost() 
	{ 
		return $this->helper->getSmtpPost();
	}

	public function getAuth() 
	{ 
		return $this->helper->getAuth(); 
	}

	public function getUsername() 
	{ 
		return $this->helper->getUsername(); 
	} 

	public function getPassword() 
	{ 
		return $this->helper->getPassword(); 
	}

	public function getCcEmail()
	{
		return $this->helper->getCcEmail();
	}

	public function getTime() 
	{ 
		return str_replace(',', ':', $this->helper->getTime());
	} 

	public function getcurrentStoreTime()
	{
		return $this->_date->date()->format('Y-m-d');
	}

	public function sendMail($text) 
	{
		$enable = $this->isEnabled();
        if ($enable == 1) {
        	$ssl = $this->getSsl();
        	$smtpHost = $this->getSmtpHost();
        	$smtpPost = $this->getSmtpPost();
        	$auth = strtolower($this->getAuth());
        	$username = $this->getUsername();
        	$password = $this->encryptor->decrypt($this->getPassword());
        	$receiveEmail = $this->getEmail();
        	$receiveCc = $this->getCcEmail();
        	if ($receiveCc != "") {
        		$cc_email = explode(",", $receiveCc);
        	}
        	$arrayConfig = [
        		'auth' => $auth,
        		'ssl' => $ssl, 
        		'post' => $smtpPost,       		
        		'username' => $username,
        		'password' => $password
        	];
        	$transport = new Zend_Mail_Transport_Smtp($smtpHost, $arrayConfig);
        	$mail = new Zend_Mail();
        	$mailTemplateId = 'myemail_email_template';
        	$var = ['time' => $this->getTime()];
        	$template = $this->emailTemplate->getTemplate($mailTemplateId, $var);
        	$mail->setFrom($username, 'Admin')->addTo($receiveEmail)->setSubject('Notification')->setBodyHtml($template);
        	if (isset($cc_email)) {
        		$mail->addCc($cc_email);
        	}
        	$mail->createAttachment($text,
        		\Zend_Mime::TYPE_OCTETSTREAM,
        		\Zend_Mime::DISPOSITION_ATTACHMENT,
        		\Zend_Mime::ENCODING_BASE64,
        		'export/export-data-' . $this->getcurrentStoreTime() . '.csv'
        	);
        	if (!$mail->send($transport) instanceof Zend_Mail) {

        	}
        }    
	}
}