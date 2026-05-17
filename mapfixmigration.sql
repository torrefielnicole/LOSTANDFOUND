-- ============================================================
--  Lost & Found — Map Fix Migration
--  Run this in phpMyAdmin > SQL tab on your XAMPP database
-- ============================================================

USE lost_found;   -- ← change to your actual DB name if different

-- 1. Add lat/lng columns to items table (stores resolved coordinates)
ALTER TABLE items
    ADD COLUMN IF NOT EXISTS lat  DECIMAL(10,7) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS lng  DECIMAL(10,7) NULL DEFAULT NULL;

-- 2. Create the geocoding cache table
CREATE TABLE IF NOT EXISTS location_cache (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    location_key  VARCHAR(255)    NOT NULL,
    lat           DECIMAL(10,7)   NOT NULL,
    lng           DECIMAL(10,7)   NOT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY   (id),
    UNIQUE KEY    uq_location (location_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Pre-seed common Inabanga locations into the cache
--    so no API call is needed for these
INSERT IGNORE INTO location_cache (location_key, lat, lng) VALUES
('inabanga market',              10.0308000, 124.0660000),
('public market',                10.0308000, 124.0660000),
('palengke',                     10.0308000, 124.0660000),
('market',                       10.0308000, 124.0660000),
('police station',               10.0320000, 124.0670000),
('inabanga police station',      10.0320000, 124.0670000),
('pnp',                          10.0320000, 124.0670000),
('main ground',                  10.0316000, 124.0669000),
('sports complex',               10.0316000, 124.0669000),
('basketball court',             10.0316000, 124.0669000),
('gymnasium',                    10.0317000, 124.0671000),
('gym',                          10.0317000, 124.0671000),
('park',                         10.0315000, 124.0668000),
('plaza',                        10.0315000, 124.0668000),
('municipal hall',               10.0318000, 124.0672000),
('town hall',                    10.0318000, 124.0672000),
('church',                       10.0312000, 124.0663000),
('simbahan',                     10.0312000, 124.0663000),
('pier',                         10.0295000, 124.0645000),
('terminal',                     10.0300000, 124.0655000),
('bus terminal',                 10.0300000, 124.0655000),
('rhu',                          10.0322000, 124.0678000),
('health center',                10.0322000, 124.0678000),
('hospital',                     10.0322000, 124.0678000),
('highway',                      10.0310000, 124.0667000),
('national highway',             10.0310000, 124.0667000),
('inabanga',                     10.0310000, 124.0667000),
('unknown',                      10.0310000, 124.0667000);

-- 4. Verify
SELECT 'Migration complete!' AS status, COUNT(*) AS cached_locations FROM location_cache;