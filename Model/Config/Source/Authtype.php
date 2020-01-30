<?php

namespace Dtn\DailyReport\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Authtype implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'none', 'label' => __('None')],
            ['value' => 'ssl', 'label' => 'SSL'],
            ['value' => 'tls', 'label' => 'TLS']
        ];
    }
}
