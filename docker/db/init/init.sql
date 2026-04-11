CREATE TABLE users (
    -- Automatically generated master key
    id SERIAL PRIMARY KEY,
    
    -- User data with basic validation
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    
    -- Optional profile field
    full_name VARCHAR(100),
    
    -- System metadata
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);