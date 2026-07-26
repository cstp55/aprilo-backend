# Database Design
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Database Design
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Relational Schema - Aprilo Platform (PostgreSQL)

```
┌──────────────────┐          ┌───────────────────┐          ┌───────────────────┐
│     tenants      │          │    admin_users    │          │   chat_sessions   │
├──────────────────┤          ├───────────────────┤          ├───────────────────┤
│ PK  id           │◄──┐      │ PK  id            │◄──┐      │ PK  id            │◄──┐
│     store_url    │   │      │     email         │   │      │     tenant_id     │───┘
│     api_key_hash │   │      │     tenant_id     │───┘      │     customer_id   │
│     created_at   │   │      │     role (admin)  │          │     status        │
└──────────────────┘   │      └───────────────────┘          └─────────▲─────────┘
                       │                                               │
                       │      ┌───────────────────┐                    │
                       │      │ product_vectors   │          ┌─────────┴─────────┐
                       │      ├───────────────────┤          │   chat_messages   │
                       └───── │ FK  tenant_id     │          ├───────────────────┤
                              │     product_sku   │          │ PK  id            │
                              │     embedding     │          │ FK  session_id    │
                              └───────────────────┘          │     sender_type   │
                                                             │     message_text  │
                                                             └───────────────────┘
```

### 2.1. Tenants Table
Stores organization credentials and subscription levels for each connected Magento store.
```sql
CREATE TABLE tenants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    store_url VARCHAR(255) NOT NULL UNIQUE,
    api_key_hash VARCHAR(64) NOT NULL,
    webhook_secret VARCHAR(64) NOT NULL,
    config JSONB NOT NULL DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_tenants_api_key ON tenants(api_key_hash);
```

### 2.2. Admin & Support Users Table
Stores merchant dashboard users and live chat support agents.
```sql
CREATE TABLE admin_users (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'agent', -- 'admin', 'supervisor', 'agent'
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_tenant_email UNIQUE(tenant_id, email)
);
```

### 2.3. Product Vectors Table (pgvector Store)
Maintains high-dimensional embeddings for catalog items to enable semantic product discovery.
```sql
-- Ensure the pgvector extension is enabled
CREATE EXTENSION IF NOT EXISTS vector;

CREATE TABLE product_vectors (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    product_id VARCHAR(50) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    attributes JSONB NOT NULL DEFAULT '{}',
    search_payload TEXT NOT NULL, -- Concatenated attributes (Name, Desc, Price, Specs)
    embedding vector(768) NOT NULL, -- Gemini Text Embedding model size
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_tenant_product UNIQUE(tenant_id, product_id)
);

-- HNSW Vector Index for Cosine Similarity
CREATE INDEX idx_product_vectors_hnsw ON product_vectors 
USING hnsw (embedding vector_cosine_ops) 
WITH (m = 16, ef_construction = 64);
```

### 2.4. Chat Sessions Table
Tracks customer conversations across the storefront widget.
```sql
CREATE TABLE chat_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    magento_customer_id INTEGER NULL, -- NULL if guest session
    customer_email VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'ai_active', -- 'ai_active', 'escalated', 'agent_active', 'closed'
    assigned_agent_id INTEGER REFERENCES admin_users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_chat_sessions_tenant_status ON chat_sessions(tenant_id, status);
```

### 2.5. Chat Messages Table
Maintains granular history of all exchanges, including RAG context and metadata.
```sql
CREATE TABLE chat_messages (
    id SERIAL PRIMARY KEY,
    session_id UUID NOT NULL REFERENCES chat_sessions(id) ON DELETE CASCADE,
    sender_type VARCHAR(50) NOT NULL, -- 'customer', 'ai', 'system', 'agent'
    message_text TEXT NOT NULL,
    citations JSONB NULL, -- PDP URLs, SKU metadata referenced by AI
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_chat_messages_session ON chat_messages(session_id);
```

---

## 3. Database Schema - Magento Plugin (MySQL)
Configured using Magento's declarative schema (`db_schema.xml`).

### 3.1. `aprilo_sync_queue` (Change Data Capture Queue)
Logs incremental additions, updates, or deletions of catalog records for execution by the sync cron.
```sql
CREATE TABLE aprilo_sync_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL, -- 'product', 'category', 'cms'
    entity_id INT UNSIGNED NOT NULL,
    action VARCHAR(20) NOT NULL, -- 'insert', 'update', 'delete'
    status TINYINT NOT NULL DEFAULT 0, -- 0=Pending, 1=Success, 2=Failed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sync_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
