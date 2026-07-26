-- PostgreSQL Database Schema for Aprilo Platform Backend
-- Setup tenants, licensing verification, pgvector, and chat sessions

CREATE EXTENSION IF NOT EXISTS vector;

-- Tenants Table
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

-- Licenses Table for Paid User Verification (Enforces single domain lock)
CREATE TABLE licenses (
    id SERIAL PRIMARY KEY,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    tenant_id INTEGER REFERENCES tenants(id) ON DELETE SET NULL,
    allowed_domain VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'inactive', -- 'inactive', 'active', 'suspended'
    activated_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for licensing checks
CREATE INDEX idx_licenses_key_domain ON licenses(license_key, allowed_domain);

-- Product Vectors for Semantic Search (RAG)
CREATE TABLE product_vectors (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    product_id VARCHAR(50) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    attributes JSONB NOT NULL DEFAULT '{}',
    search_payload TEXT NOT NULL,
    embedding vector(768) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_tenant_product UNIQUE(tenant_id, product_id)
);

-- HNSW Vector Index for Cosine Similarity search
CREATE INDEX idx_product_vectors_hnsw ON product_vectors 
USING hnsw (embedding vector_cosine_ops) 
WITH (m = 16, ef_construction = 64);

-- Chat Sessions
CREATE TABLE chat_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    magento_customer_id INTEGER NULL,
    customer_email VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'ai_active', -- 'ai_active', 'escalated', 'agent_active', 'closed'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Chat Messages
CREATE TABLE chat_messages (
    id SERIAL PRIMARY KEY,
    session_id UUID NOT NULL REFERENCES chat_sessions(id) ON DELETE CASCADE,
    sender_type VARCHAR(50) NOT NULL, -- 'customer', 'ai', 'agent', 'system'
    message_text TEXT NOT NULL,
    citations JSONB NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
