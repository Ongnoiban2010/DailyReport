<?php

namespace Dtn\DailyReport\Cron;

use Dtn\DailyReport\Helper\Data; 
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Zend_Mail;
use Zend_Mail_Transport_Smtp;
use Magento\Framework\Exception\LocalizedException;
use Dtn\DailyReport\Model\Mail\EmailTemplate;

class Test
{
	protected $orderRepository;

	protected $_collection;

	protected $helper; 

	protected $_date;

	protected $_stockItemRepository;

	protected $directory;

	private $encryptor;

	protected $emailTemplate;

	public function __construct(
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\ResourceModel\Order\Item\Collection $collection,
		Data $helper,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Filesystem $filesystem,
		TimezoneInterface $date,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor,
		EmailTemplate $emailTemplate
	) {
		$this->orderRepository = $orderRepository;
		$this->_collection = $collection;
		$this->helper = $helper;
		$this->_fileFactory = $fileFactory;
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); 
		$this->_date =  $date;
		$this->_stockItemRepository = $stockItemRepository;
		$this->encryptor = $encryptor;
		$this->emailTemplate = $emailTemplate;
	}

	public function getDate() 
	{ 
		return $this->helper->getDate(); 
	} 

	public function getTime() 
	{ 
		return str_replace(',', ':', $this->helper->getTime());
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

	public function getcurrentStoreTime()
	{
		return $this->_date->date()->format('Y-m-d');
	}

	public function getCcEmail()
	{
		return $this->helper->getCcEmail();
	}

	public function getTimezone()
	{
		return $this->helper->getTimezone();
	}

	public function export()
	{
		$name = date('m-d-Y-H-i-s');
		$filepath = 'export/export-data-' .$this->getcurrentStoreTime(). '.csv'; 
        // at Directory path Create a Folder Export and FIle
		$this->directory->create('export');
		$stream = $this->directory->openFile($filepath, 'w+');
		$stream->lock();
        //column name dispay in your CSV 
		$columns = ['Name','Sku','Qty_shipped','Qty_Stock'];
		foreach ($columns as $column) 
		{
            $header[] = $column; //storecolumn in Header array
        }
        $stream->writeCsv($header);
        $test = 'Name, Sku, Qty_shipped, Qty_Stock'."\n";
        if ($this->getProduct() != null) {
        	foreach($this->getProduct() as $item) {
        		$itemData = [];
        		$itemData[] = $item['name'];
        		$itemData[] = $item['sku'];
        		$itemData[] = $item['qty_ordered'];
        		$itemData[] = $item['stock'];
        		$stream->writeCsv($itemData);
        		$test .= implode(',',$item)."\n";
        	}
        }
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
        $csvfilename = 'locator-import-'.$name.'.csv';      
        // config send email
        $enable = $this->isEnabled();
        if($enable == 1) {
        	$ssl = $this->getSsl();
        	$smtpHost = $this->getSmtpHost();
        	$smtpPost = $this->getSmtpPost();
        	$auth = strtolower($this->getAuth());
        	$username = $this->getUsername();
        	$password = $this->encryptor->decrypt($this->getPassword());
        	$receiveEmail = $this->getEmail();
        	$receiveCc = $this->getCcEmail();
        	if($receiveCc != "") {
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
        	$mail->createAttachment($test,
        		\Zend_Mime::TYPE_OCTETSTREAM,
        		\Zend_Mime::DISPOSITION_ATTACHMENT,
        		\Zend_Mime::ENCODING_BASE64,
        		$filepath
        	);
        	if(!$mail->send($transport) instanceof Zend_Mail) {

        	}
        	// return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);	
        }    
    }

    /**
     * [getStockItem description]
     * @param  [type] $productId [description]
     * @return [type]            [description]
     */
    public function getStockItem($productId)
    {
    	return $this->_stockItemRepository->get($productId);
    }

    public function getProduct()
    {    		
    	$dateStoreTime = $this->getcurrentStoreTime().' '.$this->getTime();
    	$dateConvertUtc = date_create($dateStoreTime,timezone_open($this->getTimezone()));
    	date_timezone_set($dateConvertUtc,timezone_open("UTC"));
    	$to = date_format($dateConvertUtc,"Y-m-d H:i:s");
    	$from = strtotime('-1 day', strtotime($to));
	    $from = date('Y-m-d h:i:s', $from); // 1 days before	        
	    $orders = $this->_collection->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to));
	    $data = [];
	    if (count($orders) > 0) {
	    	foreach ($orders as $order) {
	    		$orderId = $order->getOrder_id();
	    		if ($this->orderRepository->get($orderId)->getStatus() === 'processing') {
	    			$_productStock = $this->getStockItem($order->getProduct_id());	
	    			$data[$order->getProduct_id()] = [
	    					'name' => $order->getName(),
	    					'sku' => $order->getSku(),
	    					'qty_ordered' => $order->getQty_ordered(),
	    					'stock' => $_productStock->getQty()
	    				];
	    		}
	    	}
	    }	
	    print_r($data);      
	    return $data;
	}
}