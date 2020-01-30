<?php

namespace Dtn\DailyReport\Cron;

use Dtn\DailyReport\Helper\Data; 
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Zend_Mail;
use Zend_Mail_Transport_Smtp;

class Test
{
	protected $orderRepository;

	protected $itemFactory;

	protected $helper; 

	protected $_date;

	protected $_stockItemRepository;

	private $encryptor;

	public function __construct(
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\Order\ItemFactory $itemFactory,
		Data $helper,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Filesystem $filesystem,
		TimezoneInterface $date,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->orderRepository = $orderRepository;
		$this->itemFactory = $itemFactory;
		$this->helper = $helper;
		$this->_fileFactory = $fileFactory;
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); 
		$this->_date =  $date;
		$this->_stockItemRepository = $stockItemRepository;
		$this->encryptor = $encryptor;
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

	public function export()
	{

		$name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$this->getcurrentStoreTime(). '.csv'; // at Directory path Create a Folder Export and FIle
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

         // get Collection of Table data 
        $test = 'Name, Sku, Qty_shipped, Qty_Stock'."\n";
        foreach($this->getProduct() as $item)
        {
        	$itemData = [];
            // column name must same as in your Database Table 
        	$itemData[] = $item['name'];
        	$itemData[] = $item['sku'];
        	$itemData[] = $item['qty_ordered'];
        	$itemData[] = $item['stock'];
        	$stream->writeCsv($itemData);
        	$test .= implode(',',$item)."\n";
        }
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
        $csvfilename = 'locator-import-'.$name.'.csv';      
        // cau hinh gui email
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
        	$cc_email = explode(",", $receiveCc);
        	$arrayConfig = [
        		'auth' => $auth,
        		'ssl' => $ssl, 
        		'post' => $smtpPost,       		
        		'username' => $username,
        		'password' => $password
        	];
        	$transport = new Zend_Mail_Transport_Smtp($smtpHost, $arrayConfig);
        	$mail = new Zend_Mail();
        	$htmlBody = 'Hello <br> Please check the attached file for a report of products sold within 24 hours of '.$this->getTime().' yesterday';
        	$mail->setFrom($username, 'Admin')->addTo($receiveEmail)->setSubject('Notification')->setBodyHtml($htmlBody)->addCc($cc_email);
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
    	// $to2 = $this->getcurrentStoreTime()." ".$this->getTime();

    	$obj = \Magento\Framework\App\ObjectManager::getInstance();
    	$dateTime = $obj->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
    	$currentDate = $dateTime->gmtDate('Y-m-d');
    	
  //   	$datetime = $this->getcurrentStoreTime()." ".$this->getTime();
  //   	$date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
		// echo $date_utc->format(\DateTime::RFC850);

    	$to = $currentDate.' '.$this->getTime();
    	echo $to;

    	$d1 = date_create($to, timezone_open("UTC"));
    	$date = date_format($d1,"Y-m-d H:i:s");

    	$from = strtotime('-1 day', strtotime($date));
	    $from = date('Y-m-d h:i:s', $from); // 1 days before	        
	    $orders = $this->itemFactory->create()->getCollection()->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$date));
	    $data = [];
	    if (count($orders) > 0) {
	    	foreach ($orders as $order) {
	    		$orderId = $order->getOrder_id();
	    		if($this->orderRepository->get($orderId)->getStatus() === 'processing') {	
	    			$_productStock = $this->getStockItem($order->getProduct_id());
	    			$index = $this->combineProduct($data, $order->getSku());
	    			if($index !== false) {
	    				$a = $order->getQty_ordered() + $data[$index]['qty_ordered'];
	    				$data[$index] = [
	    					'name' => $order->getName(),
	    					'sku' => $order->getSku(),
	    					'qty_ordered' => $a,
	    					'stock' => $_productStock->getQty()
	    				];
	    			} else {
	    				$data[] = [
	    					'name' => $order->getName(),
	    					'sku' => $order->getSku(),
	    					'qty_ordered' => $order->getQty_ordered(),
	    					'stock' => $_productStock->getQty()
	    				];
	    			}
	    		}
	    	}
	    }	 
	    print_r($data);      
	    return $data;
	}

	public function combineProduct($arr, $sku)
	{
		$ind = false;
		foreach ($arr as $key => $value) {
			if($value['sku'] == $sku) {
				$ind = $key;
			}
		}
		return $ind;
	}

}