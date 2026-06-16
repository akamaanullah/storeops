-- Add 'pending' to payment type enum
ALTER TABLE `payments`
  MODIFY COLUMN `type` ENUM('full', 'partial', 'pending') NOT NULL;
