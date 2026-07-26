<?php
/**
 * Aprilo Commerce Assistant
 * Config Dropdown Source Model
 */
namespace Aprilo\CommerceAssistant\Model\Config\Source;

class Position implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'right', 'label' => __('Bottom Right')],
            ['value' => 'left', 'label' => __('Bottom Left')]
        ];
    }
}
