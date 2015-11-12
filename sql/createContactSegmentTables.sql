CREATE TABLE IF NOT EXISTS civicrm_contact_segment (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(75) NULL,
  parent_id INT NULL,
  is_active TINYINT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC))
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS civicrm_contact_segment_role (
  id INT NOT NULL AUTO_INCREMENT,
  segment_id INT DEFAULT NULL,
  contact_id INT DEFAULT NULL,
  role_type_id INT DEFAULT 1,
  start_date DATE,
  end_date DATE,
  is_active TINYINT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC),
  INDEX category (segment_id ASC),
  INDEX contact (contact_id ASC),
  INDEX segment_contact (segment_id ASC, contact_id ASC))
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS civicrm_contact_segment_setting (
  id INT NOT NULL AUTO_INCREMENT,
  label VARCHAR(80) NULL,
  child_label VARCHAR(80) NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC))
  ENGINE = InnoDB;