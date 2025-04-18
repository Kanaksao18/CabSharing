-- Add formatted address columns to rides table
ALTER TABLE rides
ADD COLUMN source_formatted VARCHAR(255) AFTER source,
ADD COLUMN destination_formatted VARCHAR(255) AFTER destination; 