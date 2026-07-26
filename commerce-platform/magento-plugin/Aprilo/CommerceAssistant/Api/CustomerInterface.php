<?php
/**
 * Aprilo Commerce Assistant
 * Customer API interface
 */
namespace Aprilo\CommerceAssistant\Api;

interface CustomerInterface
{
    /**
     * Get details for the currently authenticated customer, including profile metadata and past orders.
     *
     * @return \Aprilo\CommerceAssistant\Api\Data\CustomerDetailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDetails();

    /**
     * Save/update a customer default shipping address in the address book.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAddress($address);
}
