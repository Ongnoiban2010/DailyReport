<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dtn\DailyReport\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Export extends \Magento\Framework\App\Action\Action
{
/**
* @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
*/
protected $_productCollectionFactory;
    
public function __construct(
     Context $context,
     \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
) {
     $this->_productCollectionFactory = $productCollectionFactory;
     parent::__construct($context);
}
public function execute()
{
     $notificationId = $this->getRequest()->getParam('notification_id');
     $heading = [
         __('Id'),
         __('SKU'),
         __('Name')
     ];
     $outputFile = "ListProducts". date('Ymd_His').".csv";
     $handle = fopen($outputFile, 'w');
     fputcsv($handle, $heading);
     $productCollection = $this->_productCollectionFactory->create()->addAttributeToFilter('entity_id',array('gt'=>5));
     foreach ($productCollection as $product) {
         $row = [
             $product->getId(),
             $product->getSku(),
             $product->getName()           
         ];
         fputcsv($handle, $row);
     }
     $this->downloadCsv($outputFile);
}
 
public function downloadCsv($file)
{
     if (file_exists($file)) {
         //set appropriate headers
         header('Content-Description: File Transfer');
         header('Content-Type: application/csv');
         header('Content-Disposition: attachment; filename='.basename($file));
         header('Expires: 0');
         header('Cache-Control: must-revalidate');
         header('Pragma: public');
         header('Content-Length: ' . filesize($file));
         ob_clean();flush();
         readfile($file);
     }
}
}