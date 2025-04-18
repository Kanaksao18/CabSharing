-- Add car_model and car_number columns to users table
ALTER TABLE users
ADD COLUMN car_model VARCHAR(100) AFTER phone,
ADD COLUMN car_number VARCHAR(20) AFTER car_model; 