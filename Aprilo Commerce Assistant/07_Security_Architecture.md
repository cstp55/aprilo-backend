# Security Architecture
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Security Architecture
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Authentication & Authorization Matrix

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 Security Architecture                                  │
├─────────────────────────┬─────────────────────────┬────────────────────────────────────┤
│ Connection Channel      │ Protocol / Type         │ Security Measure                   │
├─────────────────────────┼─────────────────────────┼────────────────────────────────────┤
│ Storefront -> Widget    │ Public API Key          │ CORS Restriction, Rate Limiting    │
│ Platform -> Magento     │ HMAC-SHA256 Signature   │ Payload Signature Validation       │
│ Magento -> Platform     │ Client Secret JWT       │ Bearer Authentication Token        │
│ Widget -> Platform (Auth)│ Session JWT             │ Ephemeral Customer Session Tokens  │
└─────────────────────────┴─────────────────────────┴────────────────────────────────────┘
```

### 2.1. Storefront Widget to Aprilo Platform
* **Mechanism:** The Javascript widget loaded on the storefront communicates with Aprilo using a Public API Key.
* **Mitigation:** Access is restricted to configured storefront domains using strict **CORS (Cross-Origin Resource Sharing)** headers. Anonymous session limits are enforced per IP to prevent DoS attacks on the LLM backend.

### 2.2. Platform to Magento (Inbound API Operations)
* **Mechanism:** HMAC-SHA256 Payload Signature.
* **Details:** Every inbound payload is hashed with a shared client secret. The Magento plugin verifies the hash. If the timestamp varies by more than 300 seconds, the request is rejected to mitigate replay attacks.

### 2.3. Magento Customer Authentication Handshake
* **Mechanism:** Ephemeral JWT.
* **Details:** When a customer logs in, Magento generates a JWT containing:
  ```json
  {
    "sub": "customer_12",
    "email": "jane.doe@example.com",
    "exp": 1782392687,
    "iss": "magento_plugin"
  }
  ```
  This token is encrypted using the shared secret and stored in a secure cookie. The widget transmits this cookie, allowing the platform to call Magento REST APIs within the authenticated user context.

---

## 3. Data Protection (Encryption)
* **In-Transit:** All system communication must use **TLS 1.3**. Non-HTTPS traffic is rejected by default.
* **At-Rest:** 
  * Sensitive configuration fields on the Aprilo database (such as raw Magento admin API keys) are encrypted using **AES-256-GCM** with a master key stored in an external secrets manager.
  * Customer message logs and transcripts are encrypted at the tablespace level.

---

## 4. Magento API Permissions & Least Privilege Scopes
Magento integration credentials generated for the Aprilo platform must be restricted using Magento's **ACL (Access Control List)** module. The API integration must only be granted the following minimum permission scopes:

```xml
<!-- Scopes required in extension's integration config -->
<resources>
    <resource ref="Magento_Catalog::products"/>
    <resource ref="Magento_Catalog::categories"/>
    <resource ref="Magento_Sales::sales"/>
    <resource ref="Magento_Sales::transactions"/>
    <resource ref="Magento_Customer::customer"/>
    <resource ref="Magento_Customer::group"/>
    <resource ref="Magento_Newsletter::subscriber"/>
</resources>
```
*Any attempt to access root-level configs, billing gateway configurations, or system settings must return an HTTP 403 Forbidden error.*

---

## 5. Network Security & IP Whitelisting
To prevent unauthorized API access, the Magento plugin should support limiting requests to a defined list of IPs.
* Merchants can add Aprilo's static egress IP addresses to the allowed IP whitelist in the Magento Admin settings.
* Requests originating from other IPs will be rejected with an HTTP 401 Unauthorized status before checking the signature.
