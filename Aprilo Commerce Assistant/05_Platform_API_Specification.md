# Platform API Specification
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Platform API Specification
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Authentication & Security Headers
All API communications between the Magento Plugin and the Aprilo Platform must be authenticated and secured.

### 2.1. Inbound Requests (Platform -> Magento)
Requests are authenticated using a shared secret key via an HMAC-SHA256 signature in the request headers.

Headers:
* `Content-Type: application/json`
* `X-Aprilo-Store-Id: [STORE_ID]`
* `X-Aprilo-Timestamp: [UNIX_TIMESTAMP]`
* `X-Aprilo-Signature: [HMAC_SHA256_HEX]`

*Signature Formulation:*
```text
Signature = HMAC-SHA256(Secret_Key, Timestamp + "." + HTTP_Method + "." + Request_Path + "." + Raw_Request_Body)
```

### 2.2. Outbound Requests (Magento -> Platform)
Requests are authenticated using a JWT bearer token signed by the Magento Plugin using the Client Secret Key.

Headers:
* `Content-Type: application/json`
* `Authorization: Bearer [JWT_TOKEN]`

---

## 3. Inbound API Endpoints (Platform to Magento)

### 3.1. Get Customer details
* **Path:** `GET /rest/V1/aprilo/customer/details`
* **Query Params:** `customer_id=[ID]` or verified via JWT context.
* **Response Payload:**
```json
{
  "customer": {
    "id": 12,
    "firstname": "Jane",
    "lastname": "Doe",
    "email": "jane.doe@example.com",
    "phone": "+919876543210",
    "newsletter_subscribed": true,
    "addresses": [
      {
        "id": 45,
        "firstname": "Jane",
        "lastname": "Doe",
        "street": ["Flat 402, Highrise Apartments", "Sector 62"],
        "city": "Noida",
        "region": "Uttar Pradesh",
        "postcode": "201301",
        "country_id": "IN",
        "telephone": "+919876543210",
        "default_shipping": true,
        "default_billing": true
      }
    ]
  }
}
```

### 3.2. Update Address Book
* **Path:** `POST /rest/V1/aprilo/customer/address`
* **Request Payload:**
```json
{
  "customer_id": 12,
  "address": {
    "firstname": "Jane",
    "lastname": "Doe",
    "street": ["Villa 10", "Green Meadows"],
    "city": "Bengaluru",
    "region": "Karnataka",
    "postcode": "560102",
    "country_id": "IN",
    "telephone": "+919876543210",
    "default_shipping": true,
    "default_billing": false
  }
}
```
* **Response Payload:**
```json
{
  "success": true,
  "address_id": 46,
  "message": "Address updated successfully."
}
```

---

## 4. Outbound API Endpoints (Magento to Platform)

### 4.1. Catalog Sync Payload
* **Path:** `POST /api/v1/sync/catalog`
* **Request Payload:**
```json
{
  "batch_id": "sync_987654321",
  "products": [
    {
      "id": "104",
      "sku": "gaming-laptop-g15",
      "name": "Nitro 5 Gaming Laptop",
      "description": "High performance gaming laptop with NVIDIA RTX 3050 and 8GB RAM.",
      "short_description": "RTX 3050 Gaming Laptop",
      "price": 68999.00,
      "currency": "INR",
      "categories": ["Laptops", "Electronics", "Gaming"],
      "stock_status": "in_stock",
      "qty": 14,
      "image_url": "https://mystore.com/media/catalog/product/n/i/nitro5.jpg",
      "url_path": "electronics/laptops/nitro-5-gaming.html",
      "attributes": {
        "brand": "Acer",
        "ram": "8GB",
        "storage": "512GB SSD",
        "processor": "Intel i5"
      }
    }
  ]
}
```
* **Response Payload:**
```json
{
  "success": true,
  "processed_items": 1,
  "errors": []
}
```

### 4.2. Customer Login Event Notification
* **Path:** `POST /api/v1/events/customer-login`
* **Request Payload:**
```json
{
  "customer_id": 12,
  "email": "jane.doe@example.com",
  "login_timestamp": 1782390887,
  "session_token": "jwt_header_payload_signature_xyz"
}
```
* **Response Payload:**
```json
{
  "success": true,
  "session_id": "aprilo_sess_883294"
}
```

### 4.3. Order Status Change Notification
* **Path:** `POST /api/v1/events/order-status`
* **Request Payload:**
```json
{
  "order_id": "10000492",
  "customer_id": 12,
  "email": "jane.doe@example.com",
  "total": 68999.00,
  "currency": "INR",
  "old_status": "processing",
  "new_status": "shipped",
  "tracking": {
    "carrier": "FedEx",
    "tracking_number": "1234567890",
    "tracking_url": "https://www.fedex.com/track?num=1234567890"
  }
}
```
* **Response Payload:**
```json
{
  "success": true,
  "escalated_notifications_sent": 0
}
```
