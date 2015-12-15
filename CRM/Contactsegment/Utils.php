<?php
/**
 * Class with general static util functions for extension
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-V3.0
 */
class CRM_Contactsegment_Utils {

  /**
   * Method to delete role option group with all its values
   *
   * @access public
   * @static
   */
  public static function removeRoleOptionGroup() {
    $config = CRM_Contactsegment_Config::singleton();
    $optionGroupId = $config->getRoleOptionGroup('id');
    try {
      $optionValues = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $optionGroupId));
      foreach ($optionValues['values'] as $optionValueId => $optionValue) {
        try {
          civicrm_api3('OptionValue', 'Delete', array('id' => $optionValueId));
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    try {
      civicrm_api3('OptionGroup', 'Delete', array('id' => $optionGroupId));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to create or update option value
   *
   * @param $params
   * @return array|bool
   * @access public
   * @static
   */
  public static function createOptionValue($params) {
    if (empty($params) || !$params['name'] || !$params['option_group_id']) {
      return FALSE;
    }
    $optionValueId = self::getOptionValueId($params['option_group_id'], $params['name']);
    if ($optionValueId) {
      $params['id'] = $optionValueId;
    }
    try {
      return civicrm_api3('OptionValue', 'Create', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to get option value id with option group id and name
   *
   * @param int $optionGroupId
   * @param string $optionValueName
   * @return array|bool
   * @access public
   * @static
   */
  public static function getOptionValueId($optionGroupId, $optionValueName) {
    $params = array(
      'option_group_id' => $optionGroupId,
      'name' => $optionValueName,
      'return' => 'id');
    try {
      return civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to retrieve option group by name
   *
   * @param string $optionGroupName
   * @return array|bool
   * @access public
   * @static
   */
  public static function getOptionGroupWithName($optionGroupName) {
    if (empty($optionGroupName)) {
      return FALSE;
    }
    try {
      return civicrm_api3('OptionGroup', 'Getsingle', array('name' => $optionGroupName));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to create option group
   *
   * @param $params
   * @return array|bool
   * @access public
   * @static
   */
  public static function createOptionGroup($params) {
    if (empty($params) || !isset($params['name']) || empty($params['name'])) {
      return FALSE;
    }
    if (!$params['title']) {
      $params['title'] = $params['name'];
    }
    if (!$params['description']) {
      $params['description'] = "";
    }
    try {
     return civicrm_api3('OptionGroup', 'Create', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to determine max key in navigation menu (core solutions do not cater for child keys!)
   *
   * @param array $menuItems
   * @return int $maxKey
   */
  public static function getMaxMenuKey($menuItems) {
    $maxKey = 0;
    foreach ($menuItems as $menuKey => $menuItem) {
      if ($menuKey > $maxKey) {
        $maxKey = $menuKey;
      }
      if (isset($menuItem['child'])) {
        foreach ($menuItem['child'] as $childKey => $child) {
          if ($childKey > $maxKey) {
            $maxKey = $childKey;
          }
        }
      }
    }
    return $maxKey;
  }

  /**
   * Method to generate a name from a label, replacing some chars with "_"
   *
   * @param $label
   * @return mixed
   */
  public static function generateNameFromLabel($label) {
    if (empty($label)) {
      return $label;
    }
    $name = strtolower($label);
    $config = CRM_Contactsegment_Config::singleton();
    foreach ($config->getReplaceableChars() as $char) {
      $name = str_replace($char, "_", $name);
      $name = str_replace("__", "_", $name);
    }
    return $name;
  }

  public static function getRoleLabel($roleValue) {
    $config = CRM_Contactsegment_Config::singleton();
    $params = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'value' => $roleValue,
      'return' => 'label'
    );
    return civicrm_api3('OptionValue', 'Getvalue', $params);
  }

  /**
   * Method to get list of possible roles for a segment
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   *
   */
  public static function getRoleList() {
    $roleList = array();
    $config = CRM_Contactsegment_Config::singleton();
    $optionValueParams = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'is_active' => 1
    );
    $roles = civicrm_api3('OptionValue', 'Get', $optionValueParams);
    foreach ($roles['values'] as $optionValueId => $optionValue) {
      $roleList[$optionValue['value']] = $optionValue['label'];
    }
    return $roleList;
  }

  /**
   * Method to get list of segment parents
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function getParentList() {
    $parentList = array();
    $parents = civicrm_api3('Segment', 'Get', array('parent_id' => 'null'));
    foreach ($parents['values'] as $parentId => $parent) {
      $parentList[$parentId] = $parent['label'];
    }
    asort($parentList);
    return $parentList;
  }

  /**
   * Method to get list of segment children with parent id
   *
   * @param int $parentId
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function getChildList($parentId = NULL) {
    if (!$parentId) {
      $query = 'SELECT id FROM civicrm_segment WHERE parent_id IS NULL ORDER BY label ASC LIMIT 1';
      $parentId = CRM_Core_DAO::singleValueQuery($query);
    }
    $childList = array();
    $children = civicrm_api3('Segment', 'Get', array('parent_id' => $parentId));
    foreach ($children['values'] as $childId => $child) {
      $childList[$childId] = $child['label'];
    }
    asort($childList);
    return $childList;
  }
}