<?php
/**
 * Aprilo Commerce Assistant
 * Storefront Widget Block Helper
 */
namespace Aprilo\CommerceAssistant\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Widget extends Template
{
    protected $scopeConfig;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Check if module is enabled in configuration
     *
     * @return bool
     */
    public function isWidgetActive()
    {
        return (bool)$this->scopeConfig->getValue(
            'aprilo_commerce/general/active',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Compile widget settings to pass to storefront JS
     *
     * @return array
     */
    public function getWidgetParams()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $iconPath = $this->scopeConfig->getValue('aprilo_commerce/widget/launcher_icon', ScopeInterface::SCOPE_STORE);
        $fullIconUrl = $iconPath ? $mediaUrl . 'aprilo/widget/' . $iconPath : '';

        return [
            'licenseKey' => $this->scopeConfig->getValue('aprilo_commerce/general/license_key', ScopeInterface::SCOPE_WEBSITE),
            'platformUrl' => $this->scopeConfig->getValue('aprilo_commerce/general/platform_url', ScopeInterface::SCOPE_STORE),
            'widgetName' => $this->scopeConfig->getValue('aprilo_commerce/widget/name', ScopeInterface::SCOPE_STORE) ?: 'Aprilo Assistant',
            'primaryColor' => $this->scopeConfig->getValue('aprilo_commerce/widget/primary_color', ScopeInterface::SCOPE_STORE) ?: '#1A73E8',
            'position' => $this->scopeConfig->getValue('aprilo_commerce/widget/position', ScopeInterface::SCOPE_STORE) ?: 'right',
            'greeting' => $this->scopeConfig->getValue('aprilo_commerce/widget/greeting', ScopeInterface::SCOPE_STORE) ?: 'Hello!',
            'customerEmail' => $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : null,
            'customerToken' => $this->customerSession->isLoggedIn() ? $this->customerSession->getSessionId() : null,
            'iconUrl' => $fullIconUrl
        ];
    }
}
