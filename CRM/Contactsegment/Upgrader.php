<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Contactsegment_Upgrader extends CRM_Contactsegment_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Add tables to database at install
   */
  public function install() {
    $this->executeSqlFile("sql/createContactSegmentTables.sql");
  }

  /**
   * Drop database tables at uninstall
   */
  public function uninstall() {
   $this->executeSqlFile('sql/dropContactSegmentTables.sql');
  }

  /**
   * Upgrade 1001 - add civicrm_segment_tree
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');
    CRM_Core_DAO::executeQuery(
      "CREATE TABLE IF NOT EXISTS civicrm_segment_tree (id INT UNSIGNED NOT NULL)");
    CRM_Contactsegment_BAO_Segment::buildSegmentTree();
    return TRUE;
  }
  
  public function upgrade_1002() {
    CRM_Core_DAO::executeQuery(
      "ALTER TABLE civicrm_segment 
        ADD COLUMN
         is_active TINYINT UNSIGNED DEFAULT '1'
        AFTER parent_id "
    );
    return true;
  }
}
