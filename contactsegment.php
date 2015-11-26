<?php
require_once 'contactsegment.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactsegment_civicrm_config(&$config) {
  _contactsegment_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contactsegment_civicrm_xmlMenu(&$files) {
  _contactsegment_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactsegment_civicrm_install() {
  _contactsegment_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contactsegment_civicrm_uninstall() {
  require_once 'CRM/Contactsegment/Utils.php';
  require_once 'CRM/Contactsegment/Config.php';
  CRM_Contactsegment_Utils::removeRoleOptionGroup();
  _contactsegment_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactsegment_civicrm_enable() {
  // instantiate config to create option group if not exists
  CRM_Contactsegment_Config::singleton();
  _contactsegment_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contactsegment_civicrm_disable() {
  _contactsegment_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function contactsegment_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contactsegment_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contactsegment_civicrm_managed(&$entities) {
  _contactsegment_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contactsegment_civicrm_caseTypes(&$caseTypes) {
  _contactsegment_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contactsegment_civicrm_angularModules(&$angularModules) {
_contactsegment_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contactsegment_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contactsegment_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook civicrm_navigationMenu
 * to add a contact segment menu item to the Administer/Customize Data and Screens menu
 *
 * @param array $params
 */
function contactsegment_civicrm_navigationMenu( &$params ) {
  $maxKey = CRM_Contactsegment_Utils::getMaxMenuKey($params);
  $menuAdministerId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');
  $menuParentId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Customize Data and Screens', 'id', 'name');
  $params[$menuAdministerId]['child'][$menuParentId]['child'][$maxKey+1] =
    array (
    'attributes' => array (
      'label'      => ts('Manage Contact Segments'),
      'name'       => ts('Manage Contact Segments'),
      'url'        => NULL,
      'permission' => NULL,
      'operator'   => NULL,
      'separator'  => NULL,
      'parentID'   => $menuParentId,
      'navID'      => $maxKey+1,
      'active'     => 1
    ),
    'child' => array(
        1 => array(
          'attributes' => array (
            'label'      => ts('Contact Segment Settings'),
            'name'       => ts('Contact Segment Settings'),
            'url'        => CRM_Utils_System::url('civicrm/segmentsetting', 'reset=1', true),
            'permission' => 'administer CiviCRM',
            'operator'   => NULL,
            'separator'  => NULL,
            'parentID'   => $maxKey+1,
            'navID'      => 1,
            'active'     => 1
          ),
        ),
        2 => array(
          'attributes' => array (
            'label'      => ts('Contact Segments'),
            'name'       => ts('Contact Segments'),
            'url'        => CRM_Utils_System::url('civicrm/segmentlist', 'force=1&crmRowCount=25', true),
            'permission' => 'administer CiviCRM',
            'operator'   => NULL,
            'separator'  => NULL,
            'parentID'   => $maxKey+1,
            'navID'      => 2,
            'active'     => 1
          ))));
}
