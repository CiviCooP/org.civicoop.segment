CREATE TABLE IF NOT EXISTS civicrm_segment (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(128) NULL,
  label VARCHAR(128) NULL,
  parent_id INT UNSIGNED NULL,
  is_active TINYINT UNSIGNED DEFAULT '1',
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC))
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS civicrm_segment_tree (
  id INT UNSIGNED NOT NULL);

CREATE TABLE IF NOT EXISTS civicrm_contact_segment (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  contact_id INT UNSIGNED DEFAULT NULL,
  segment_id INT UNSIGNED DEFAULT NULL,
  role_value VARCHAR(512) DEFAULT NULL,
  start_date DATE,
  end_date DATE,
  is_active TINYINT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC),
  KEY `fk_segment` (`segment_id`),
  KEY `fk_segment_contact` (`contact_id`),
  CONSTRAINT `fk_segment` FOREIGN KEY (`segment_id`) REFERENCES `civicrm_segment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_segment_contact` FOREIGN KEY(`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE)
  ENGINE = InnoDB;
