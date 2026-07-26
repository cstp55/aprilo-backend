<?php
/**
 * Aprilo Commerce Assistant
 * Sync button element renderer
 */
namespace Aprilo\CommerceAssistant\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncButton extends Field
{
    /**
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Aprilo_CommerceAssistant::system/config/sync.phtml');
        }
        return $this;
    }

    /**
     * Render button
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get URL for sync trigger action
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('aprilo/sync/catalog');
    }
}
