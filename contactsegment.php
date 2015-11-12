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
  // instantiate config to create option group if not exists
  CRM_Contactsegment_Config::singleton();
  _contactsegment_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contactsegment_civicrm_uninstall() {
  _contactsegment_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactsegment_civicrm_enable() {
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
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function contactsegment_civicrm_preProcess($formName, &$form) {

}

/**
 * Implements hook_civicrm_navigationMenu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function contactsegment_civicrm_navigationMenu(&$params) {

  //add menu entry for Contact Segment Settings to Administer>CiviContribute menu
  $contactSegmentSettingsUrl = 'civicrm/contactsegmentsetting';
  // now, by default we want to add it to the CiviCRM Administer/Customize Data and Screens menu -> find it
  $administerMenuId = 0;
  $administerCustomizeMenuId = 0;
  foreach ($params as $key => $value) {
    if ($value['attributes']['name'] == 'Administer') {
      $administerMenuId = $key;
      foreach ($params[$administerMenuId]['child'] as $childKey => $childValue) {
        if ($childValue['attributes']['name'] == 'Customize Data and Screens') {
          $administerCustomizeMenuId = $childKey;
          break;
        }
      }
      break;
    }
  }
  if (empty($administerMenuId)) {
    error_log('org.civicoop.contactsegment: Cannot find parent menu Administer/Customize Data and Screens for ' . $contactSegmentSettingsUrl);
  } else {
    $contactSegmentSettingsMenu = array(
      'label' => ts('Contact Segment Settings', array('domain' => 'org.civicoop.contactsegment')),
      'name' => 'Contact Segment Settings',
      'url' => $contactSegmentSettingsUrl,
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'parentID' => $administerCustomizeMenuId,
      'navID' => CRM_Contactsegment_Utils::createUniqueNavID($params[$administerMenuId]['child']),
      'active' => 1
    );
    CRM_Contactsegment_Utils::addNavigationMenuEntry($params[$administerMenuId]['child'][$administerCustomizeMenuId],
      $contactSegmentSettingsMenu);
  }
}