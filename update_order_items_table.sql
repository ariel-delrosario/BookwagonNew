-- SQL script to modify the order_items table structure
-- This ensures purchase_type is ENUM with proper constraints

-- First, modify the column to set a default value and NOT NULL constraint
ALTER TABLE order_items 
MODIFY COLUMN purchase_type ENUM('buy', 'rent') NOT NULL DEFAULT 'buy';

-- Create an index on purchase_type for faster lookups
ALTER TABLE order_items
ADD INDEX idx_purchase_type (purchase_type);

-- Update any remaining empty values to 'buy'
UPDATE order_items
SET purchase_type = 'buy'
WHERE purchase_type = '';

-- Create triggers to ensure purchase_type is never empty on insert/update
DELIMITER //

-- Drop triggers if they already exist
DROP TRIGGER IF EXISTS before_order_item_insert //
DROP TRIGGER IF EXISTS before_order_item_update //

-- Create trigger for INSERT operations
CREATE TRIGGER before_order_item_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    -- If rental_weeks is set, default to 'rent'
    IF NEW.rental_weeks > 0 AND (NEW.purchase_type = '' OR NEW.purchase_type IS NULL) THEN
        SET NEW.purchase_type = 'rent';
    -- Otherwise default to 'buy'
    ELSEIF NEW.purchase_type = '' OR NEW.purchase_type IS NULL THEN
        SET NEW.purchase_type = 'buy';
    END IF;
END//

-- Create trigger for UPDATE operations
CREATE TRIGGER before_order_item_update
BEFORE UPDATE ON order_items
FOR EACH ROW
BEGIN
    -- If rental_weeks is set, default to 'rent'
    IF NEW.rental_weeks > 0 AND (NEW.purchase_type = '' OR NEW.purchase_type IS NULL) THEN
        SET NEW.purchase_type = 'rent';
    -- Otherwise default to 'buy'
    ELSEIF NEW.purchase_type = '' OR NEW.purchase_type IS NULL THEN
        SET NEW.purchase_type = 'buy';
    END IF;
END//

DELIMITER ;

-- Verify the changes
SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN purchase_type = 'buy' THEN 1 ELSE 0 END) as buy_items,
    SUM(CASE WHEN purchase_type = 'rent' THEN 1 ELSE 0 END) as rent_items,
    SUM(CASE WHEN purchase_type = '' OR purchase_type IS NULL THEN 1 ELSE 0 END) as empty_items
FROM order_items; 