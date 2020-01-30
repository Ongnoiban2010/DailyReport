<?php

namespace Dtn\DailyReport\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Dtn\DailyReport\Helper\Data; 

class Vidu extends Action
{

    protected $orderRepository;
    
    protected $searchCriteriaBuilder;

    protected $itemFactory;

    protected $helper; 

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        Data $helper,
        Context $context
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->itemFactory = $itemFactory;
        $this->helper = $helper; 
        parent::__construct($context);
    }

    public function getDate() 
    { 
       return $this->helper->getDate(); 
    } 

    public function execute()
    {
        $to = $this->getDate(); 
        $from = strtotime('-3 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from); // 2 days before
        $order = $this->itemFactory->create()->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))->getData();
        $data = [];
        if (count($order) > 0) {
            foreach ($order as $orders) {
                $orderId = $orders['order_id'];
                echo $orderId;
                if($this->getOrderStatus($orderId) === 'processing') {
                   $data[] = [
                     $orders['sku']
                   ];
                }
            }
        }
        print_r($data);
    }

    public function getProduct()
    {
        $to = $this->getDate(); 
        $from = strtotime('-3 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from); // 2 days before
        $order = $this->itemFactory->create()->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))->getData();
        $data = [];
        if (count($order) > 0) {
        foreach ($order as $orders) {
            $orderId = $orders['order_id'];
            if($this->getOrderStatus($orderId) === 'processing') {
                $data[] = [
                    $orders['product_id'],
                    $orders['sku'],
                    $orders['qty_shipped']
                ];
            }
        }
        return $data;
        }
    }

}
    // public function getProduct($orderID)
    // {
    //   $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderID);
    //     $orderItems = $order->getAllItems();
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $mang = [];
    //     foreach($orderItems as $item)
    //     {

    //         $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
    //         $soldproduct = $objectManager->get('Magento\Reports\Model\ResourceModel\Product\Sold\Collection')->addOrderedQty()->addAttributeToFilter('sku', $product->getSku())->setOrder('ordered_qty', 'desc')->getFirstItem(); 
    //         // $soldnumber = $soldproduct->getOrderedQty();
    //         $mang[] = $soldproduct->getData();
    //     }
    //     return $mang;
    // }
