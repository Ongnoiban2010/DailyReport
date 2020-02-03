<?php

namespace Dtn\DailyReport\Cron;

use Dtn\DailyReport\Helper\Data; 
use Dtn\DailyReport\Helper\SendEmail;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Zend_Mail;
use Zend_Mail_Transport_Smtp;

class SendMail
{
	protected $orderRepository;

	protected $_collectionFactory;

	protected $helper; 

	protected $_date;

	protected $_stockItemRepository;

	protected $directory;

	protected $sendEmail;

	private $encryptor;

	public function __construct (
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory,
		Data $helper,
		SendEmail $sendEmail,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Filesystem $filesystem,
		TimezoneInterface $date,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->orderRepository = $orderRepository;
		$this->_collectionFactory = $collectionFactory;
		$this->helper = $helper;
		$this->sendEmail = $sendEmail;
		$this->_fileFactory = $fileFactory;
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); 
		$this->_date =  $date;
		$this->_stockItemRepository = $stockItemRepository;
		$this->encryptor = $encryptor;
	}

	public function getTime() 
	{ 
		return str_replace(',', ':', $this->helper->getTime());
	} 

	public function getcurrentStoreTime()
	{
		return $this->_date->date()->format('Y-m-d');
	}

	public function getTimezone()
	{
		return $this->helper->getTimezone();
	}

	public function export()
	{
        $filepath = 'export/export-data-' . $this->getcurrentStoreTime() . '.csv'; 
        // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //column name dispay in your CSV 
        $header = ['Name','Sku','Qty_shipped','Qty_Stock'];
        $stream->writeCsv($header);
        $text = 'Name, Sku, Qty_shipped, Qty_Stock' . "\n";
        $products = $this->getProduct();
        if ($products != null) {
        	foreach($products as $item) {
	        	$itemData = [];
	        	$itemData[] = $item['name'];
	        	$itemData[] = $item['sku'];
	        	$itemData[] = $item['qty_ordered'];
	        	$itemData[] = $item['stock'];
	        	$stream->writeCsv($itemData);
	        	$text .= implode(',',$item) . "\n";
        	}
        }
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
        $csvfilename = 'locator-import-' . $this->getcurrentStoreTime() . '.csv';      
        // config send email
        $this->sendEmail->sendMail($text);
    }

    public function getStockItem($productId)
    {
    	return $this->_stockItemRepository->get($productId);
    }

    public function getProduct()
    {    		
    	$dateStoreTime = $this->getcurrentStoreTime() . ' ' . $this->getTime();
    	$dateConvertUtc = date_create($dateStoreTime,timezone_open($this->getTimezone()));
    	date_timezone_set($dateConvertUtc,timezone_open("UTC"));
    	$to = date_format($dateConvertUtc, "Y-m-d H:i:s");
    	$from = strtotime('-1 day', strtotime($to));
	    $from = date('Y-m-d h:i:s', $from);         
	    $orders = $this->_collectionFactory->create()->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to));
	    $data = [];
	    if (count($orders) > 0) {
	    	foreach ($orders as $order) {
	    		$orderId = $order->getOrder_id();
	    		$isPaid = $this->orderRepository->get($orderId)->getTotal_paid();
	    		$status = $this->orderRepository->get($orderId)->getStatus();
	    		if ($status === 'complete' && $isPaid) {
	    			$_productStock = $this->getStockItem($order->getProduct_id());
	    			if (array_key_exists ($order->getProduct_id(), $data) === true) {
	    				$a = $order->getQty_ordered() + $data[$order->getProduct_id()]['qty_ordered'];
	    				$data[$order->getProduct_id()] = [
	    					'name' => $order->getName(),
	    					'sku' => $order->getSku(),
	    					'qty_ordered' => $a,
	    					'stock' => $_productStock->getQty()
	    				];
	    			} else {
	    				$data[$order->getProduct_id()] = [
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
}