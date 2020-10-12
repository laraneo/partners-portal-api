-- ALTER TABLE users ADD token VARCHAR(255) NULL;
-- ALTER TABLE users ADD doc_id VARCHAR(255) NULL;
-- ALTER TABLE people ADD access_code VARCHAR(255) NULL;

-- ALTER TABLE users ADD share_from VARCHAR(255) NULL;
-- ALTER TABLE users ADD share_to VARCHAR(255) NULL;

-- ALTER TABLE users ADD username_legacy varchar(255) NULL;

-- ALTER TABLE users ADD group_id varchar(255) NULL;
-- ALTER TABLE users ADD is_active INT NULL;
-- ALTER TABLE users ADD role INT NULL;

ALTER TABLE users ADD last_name     varchar(255);
ALTER TABLE users ADD SyncDateModules DATETIME NULL;
ALTER TABLE users ADD SyncDateToken   DATETIME NULL;
ALTER TABLE users ADD phone_number    varchar(255) NULL;
ALTER TABLE users ADD ControlSocios_people_id int NULL;
ALTER TABLE users ADD isPartner int NULL;