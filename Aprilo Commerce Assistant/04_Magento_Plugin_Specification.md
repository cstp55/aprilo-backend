# Magento Plugin Specification
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Magento Plugin Specification
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Extension Architecture & Files
The extension is named `Aprilo_CommerceAssistant`. It resides in `app/code/Aprilo/CommerceAssistant/`.

```text
Aprilo/CommerceAssistant/
├── registration.php
├── etc/
│   ├── module.xml
│   ├── webapi.xml
│   ├── di.xml
│   ├── crontab.xml
│   ├── db_schema.xml
│   ├── events.xml
│   └── adminhtml/
│       ├── system.xml
│       └── routes.xml
├── Controller/
│   └── Api/
│       ├── Bridge.php
│       └── Customer.php
├── Observer/
│   ├── CustomerLogin.php
│   └── OrderStatusChange.php
├── Cron/
│   └── CatalogSync.php
├── Helper/
│   └── Data.php
├── Model/
│   └── Sync/
│       └── Queue.php
├── Block/
│   └── Widget.php
└── view/
    └── frontend/
        └── layout/
            └── default.xml
```

---

## 3. Configuration & Registration Files

### 3.1. `registration.php`
```php
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Aprilo_CommerceAssistant',
    __DIR__
);
```

### 3.2. `etc/module.xml`
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Aprilo_CommerceAssistant" setup_version="1.0.0">
        <sequence>
            <module name="Magento_Catalog"/>
            <module name="Magento_Sales"/>
            <module name="Magento_Customer"/>
        </sequence>
    </module>
</config>
```

---

## 4. Local Database Schema (`etc/db_schema.xml`)
The extension stores local settings, authorization tokens, and webhook sync logs.

```xml
<?xml version="1.0" encoding="utf-8"?>
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/db_schema.xsd">
    <table name="aprilo_sync_queue" resource="default" engine="innodb" comment="Aprilo Data Sync Queue Table">
        <column xsi:type="int" name="id" unsigned="true" nullable="false" identity="true" comment="Entity ID"/>
        <column xsi:type="varchar" name="entity_type" length="50" nullable="false" comment="Product, Category, Customer, CMS"/>
        <column xsi:type="int" name="entity_id" unsigned="true" nullable="false" comment="Magento Entity ID"/>
        <column xsi:type="varchar" name="action" length="20" nullable="false" comment="Insert, Update, Delete"/>
        <column xsi:type="timestamp" name="created_at" on_update="false" nullable="false" default="CURRENT_TIMESTAMP" comment="Created Time"/>
        <column xsi:type="smallint" name="status" unsigned="true" nullable="false" default="0" comment="0=Pending, 1=Success, 2=Failed"/>
        <constraint xsi:type="primary" referenceId="PRIMARY">
            <column name="id"/>
        </constraint>
        <index referenceId="APRILO_SYNC_QUEUE_STATUS" indexType="btree">
            <column name="status"/>
        </index>
    </table>
</schema>
```

---

## 5. Web API Routing (`etc/webapi.xml`)
Exposes secure custom REST endpoints for the Aprilo Platform to interact with the store.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Webapi:etc/webapi.xsd">
    <!-- Get Customer Profile & Order History -->
    <route url="/V1/aprilo/customer/details" method="GET">
        <service class="Aprilo\CommerceAssistant\Api\CustomerInterface" method="getDetails"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>
    <!-- Update Customer Address -->
    <route url="/V1/aprilo/customer/address" method="POST">
        <service class="Aprilo\CommerceAssistant\Api\CustomerInterface" method="saveAddress"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>
    <!-- Admin sync webhook -->
    <route url="/V1/aprilo/sync/trigger" method="POST">
        <service class="Aprilo\CommerceAssistant\Api\SyncInterface" method="triggerSync"/>
        <resources>
            <resource ref="Aprilo_CommerceAssistant::sync"/>
        </resources>
    </route>
</config>
```

---

## 6. Events & Observers (`etc/events.xml`)
Hook into Magento events to keep the Aprilo Platform informed in real-time.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <!-- Track Customer Logins to pass session JWT -->
    <event name="customer_login">
        <observer name="aprilo_customer_login" instance="Aprilo\CommerceAssistant\Observer\CustomerLogin"/>
    </event>
    <!-- Track order status changes for notification triggers -->
    <event name="sales_order_save_after">
        <observer name="aprilo_order_status_change" instance="Aprilo\CommerceAssistant\Observer\OrderStatusChange"/>
    </event>
</config>
```

### Observer Code Sample: `Observer/CustomerLogin.php`
```php
<?php
namespace Aprilo\CommerceAssistant\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Aprilo\CommerceAssistant\Helper\Data as Helper;

class CustomerLogin implements ObserverInterface
{
    protected $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $this->helper->notifyPlatformCustomerSession($customer);
    }
}
```

---

## 7. Cron Job Configuration (`etc/crontab.xml`)
Maintains catalog synchronization by pushing items in the queue to Aprilo.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
    <group id="default">
        <job name="aprilo_catalog_sync" instance="Aprilo\CommerceAssistant\Cron\CatalogSync" method="execute">
            <schedule>*/15 * * * *</schedule>
        </job>
    </group>
</config>
```

---

## 8. Storefront Widget Script Injection
Injects the widget script into every page layout.

### 8.1. `view/frontend/layout/default.xml`
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="before.body.end">
            <block class="Aprilo\CommerceAssistant\Block\Widget" name="aprilo_chat_widget" template="Aprilo_CommerceAssistant::widget.phtml"/>
        </referenceContainer>
    </body>
</page>
```

### 8.2. Block Helper: `Block/Widget.php`
Returns configuration params to the template (Widget URL, colors, customer JWT).
```php
<?php
namespace Aprilo\CommerceAssistant\Block;

use Magento\Framework\View\Element\Template;
use Aprilo\CommerceAssistant\Helper\Data as Helper;

class Widget extends Template
{
    protected $helper;

    public function __construct(Template\Context $context, Helper $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getWidgetConfig()
    {
        return [
            'platformUrl' => $this->helper->getPlatformUrl(),
            'apiKey' => $this->helper->getApiKey(),
            'primaryColor' => $this->helper->getPrimaryColor(),
            'launcherIcon' => $this->helper->getLauncherIcon(),
            'position' => $this->helper->getWidgetPosition(),
            'customerJwt' => $this->helper->getCurrentCustomerJwt()
        ];
    }
}
```
