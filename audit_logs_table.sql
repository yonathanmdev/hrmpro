-- Create audit_logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(36) NULL, -- UUID of the user performing the action
    action VARCHAR(255) NOT NULL, -- e.g., 'user_created', 'user_updated', 'login', etc.
    entity_type VARCHAR(100) NOT NULL, -- e.g., 'user', 'organization', 'branch'
    entity_id VARCHAR(36) NULL, -- ID of the affected entity
    old_values JSON NULL, -- Previous values (for updates)
    new_values JSON NULL, -- New values
    ip_address VARCHAR(45) NULL, -- IPv4/IPv6
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);