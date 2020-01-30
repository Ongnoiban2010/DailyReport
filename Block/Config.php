<?php 
namespace Dtn\DailyReport\Block;

use Magento\Framework\View\Element\Template; 
use Magento\Framework\View\Element\Template\Context; 
use Dtn\DailyReport\Helper\Data; 
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Config extends Template 
{ 

      protected $helper; 

      protected $_date;

      protected $_orderCollectionFactory;

      protected $orderRepository;

      protected $searchCriteriaBuilder;

      public function __construct(
       Context $context, 
       Data $helper,
       \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
       \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
       \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
       \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
      ) { 
       $this->orderRepository = $orderRepository;
       $this->searchCriteriaBuilder = $searchCriteriaBuilder;
       $this->_orderCollectionFactory = $orderCollectionFactory;
       $this->_date =  $date;
       $this->helper = $helper; 
       parent::__construct($context); 
      } 

       public function isEnabled() 
       { 
         return $this->helper->isEnabled(); 
       }

       public function getEmail() 
       { 
         return $this->helper->getEmail(); 
       }

       // public function getDate() 
       // { 
       //   return $this->helper->getDate(); 
       // } 

       public function getFrequency() 
      { 
        return $this->helper->getFrequency();
      }  
      public function getTime() 
      { 
        return str_replace(',', ':', $this->helper->getTime());
      } 
       public function getOrderCollection()
      {
        $to = $this->getDate(); // current date
        $from = strtotime('-3 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from); // 2 days before
        $orderCollection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('status', ['eq'=> 'processing'])->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to));
        return $orderCollection;
      }
}