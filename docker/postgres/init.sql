-- ============================================
-- PostgreSQL Initialization Script
-- ============================================

-- Create additional databases if needed
-- CREATE DATABASE laravel_hexagonal_academy_test;

-- Enable UUID extension (for aggregate IDs)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Enable pgcrypto for additional crypto functions
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Create schema for event store (optional, for organization)
-- CREATE SCHEMA IF NOT EXISTS event_store;

-- Grant permissions
GRANT ALL PRIVILEGES ON DATABASE laravel_hexagonal_academy TO postgres;

-- Performance tuning settings
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';
ALTER SYSTEM SET default_statistics_target = 100;
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET effective_io_concurrency = 200;
ALTER SYSTEM SET work_mem = '4MB';
ALTER SYSTEM SET min_wal_size = '1GB';
ALTER SYSTEM SET max_wal_size = '4GB';

-- Connection settings
ALTER SYSTEM SET max_connections = 200;

-- Logging for debugging (development only)
ALTER SYSTEM SET log_statement = 'all';
ALTER SYSTEM SET log_duration = on;
ALTER SYSTEM SET log_min_duration_statement = 100;

-- Reload configuration
SELECT pg_reload_conf();
