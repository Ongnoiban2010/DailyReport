<?php 
namespace Dtn\DailyReport\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory; 
use Dtn\DailyReport\Helper\Data;
use Magento\Sales\Model\Order\ItemFactory;

class Vidu2 extends Action 
{ 

  protected $helper; 

  protected $_orderCollectionFactory;

  protected $orderRepository;

  protected $itemFactory;

  public function __construct(
   Context $context, 
   Data $helper,
   \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
   \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
   ItemFactory $itemFactory, 
 ) { 
   $this->orderRepository = $orderRepository;
   $this->_orderCollectionFactory = $orderCollectionFactory;
   $this->helper = $helper; 
   $this->itemFactory = $itemFactory;
   parent::__construct($context); 
 } 

 
 public function execute() 
 {
  echo 'asd';
  }
}