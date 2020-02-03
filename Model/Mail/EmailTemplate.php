<?php
namespace Dtn\DailyReport\Model\Mail;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\Factory;

class EmailTemplate
{
    /**
     * @var TransportBuilder
     */
    protected $templateFactory;
    
    public function __construct(
        TransportBuilder $transportBuilder,
        Factory $templateFactory
    )
    {
        $this->templateFactory = $templateFactory;
    }
    public function getTemplate($mailTemplateId, $var)
    {
        return $this->templateFactory
            ->get($mailTemplateId, '')
            ->setOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setVars($var)
            ->processTemplate();
    }
}