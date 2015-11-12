<?php
/**
 * Class with general static util functions for extension
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-V3.0
 */
class CRM_Contactsegment_Utils {

  /**
   * Method creates a new, unique navID for the CiviCRM menu
   * It will consider the IDs from the database,
   * as well as the 'volatile' ones already injected into the menu
   *
   * @param array $menu
   * @return int
   * @access public
   * @static
   */
  public static function createUniqueNavID($menu) {
    $maxStoredNavId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
    $maxCurrentNavId = self::getMaxNavID($menu);
    return max($maxStoredNavId, $maxCurrentNavId) + 1;
  }

  /**
   * Method crawls the menu tree to find the (currently) biggest navID
   *
   * @param array $menu
   * @return int
   * @access public
   * @static
   */
  public static function getMaxNavID($menu) {
    $maxId = 1;
    foreach ($menu as $entry) {
      $maxId = max($maxId, $entry['attributes']['navID']);
      if (!empty($entry['child'])) {
        $maxIdChildren = self::getMaxNavID($entry['child']);
        $maxId = max($maxId, $maxIdChildren);
      }
    }
    return $maxId;
  }

  /**
   * Method to add the given menu item to the CiviCRM navigation menu if it does not exist yet.
   *
   * @param array $parentParams the params array into whose 'child' attribute the new item will be added.
   * @param array $menuEntryAttributes the attributes array to be added to the navigation menu
   * @access public
   * @static
   */
  public static function addNavigationMenuEntry(&$parentParams, $menuEntryAttributes) {
    // see if it is already in the menu...
    $menuItemSearch = array('url' => $menuEntryAttributes['url']);
    $menuItems = array();
    CRM_Core_BAO_Navigation::retrieve($menuItemSearch, $menuItems);

    if (empty($menuItems)) {
      // it's not already contained, so we want to add it to the menu

      // insert at the bottom
      $parentParams['child'][] = array(
        'attributes' => $menuEntryAttributes);
    }
  }
}