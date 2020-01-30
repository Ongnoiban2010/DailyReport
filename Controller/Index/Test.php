<?php

namespace Dtn\DailyReport\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;


class Test extends \Magento\Framework\App\Action\Action
{
    protected $uploaderFactory;

    protected $_locationFactory; 

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $locationFactory // This is returns Collaction of Data

    ) {
       parent::__construct($context);
       $this->_fileFactory = $fileFactory;
       $this->_locationFactory = $locationFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
       parent::__construct($context);
    }

    public function execute()
    {   
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV 

        $columns = ['Id','Sku2','Name','Qty','Price'];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

        $stream->writeCsv($header);

        $location = $this->_locationFactory->create();
        $location_collection = $location->getCollection(); // get Collection of Table data 

        foreach($location_collection as $item){

            $itemData = [];

            // column name must same as in your Database Table 

            $itemData[] = $item->getId();
            $itemData[] = $item->getSku();
            $itemData[] = $item->getName();
            $itemData[] = $item->getQty();
            $itemData[] = $item->getPrice();
            $stream->writeCsv($itemData);

        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'locator-import-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }


}