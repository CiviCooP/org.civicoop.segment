<?php

/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 October 2015
 * @license AGPL-3.0
 */
class CRM_Contactsegment_Config {
  static private $_singleton = NULL;
  protected $_roleOptionGroup = array();
  private $_resourcesPath = null;

  /**
   * CRM_Contactsegment_Config constructor.
   */
  function __construct() {
    $settings = civicrm_api3('Setting', 'Getsingle', array());
    $this->_resourcesPath = $settings['extensionsDir'].'/org.civicoop.contactsegment/resources/';
    $this->setRoleOptionGroup();
  }

  /**
   * Getter for role option group
   *
   * @param null $key
   * @return array
   * @access public
   */
  public function getRoleOptionGroup($key = null) {
    if (!empty($key) && is_array($this->_roleOptionGroup)) {
      return $this->_roleOptionGroup[$key];
    } else {
      return $this->_roleOptionGroup;
    }
  }

  /**
   * Method to retrieve or create option group for roles
   *
   * @throws Exception when no group found and unable to create one
   */
  private function setRoleOptionGroup() {
    $resourcesArray = $this->getJsonResourcesArray("segment_roles.json");
    $currentOptionGroup = CRM_Contactsegment_Utils::getOptionGroupWithName($resourcesArray['name']);
    if ($currentOptionGroup == FALSE) {
      $createOptionGroupParams = array(
        'name' => $resourcesArray['name'],
        'title' => $resourcesArray['title'],
        'description' => $resourcesArray['description'],
        'is_active' => $resourcesArray['is_active'],
        'is_reserved' => $resourcesArray['is_reserved']);
      $currentOptionGroup = CRM_Contactsegment_Utils::createOptionGroup($createOptionGroupParams);
      if ($currentOptionGroup == FALSE) {
        throw new Exception("Could not create a new option group with name civicoop_contact_segment nor find an existing one");
      }
    }
    $optionValues = array();
    foreach ($resourcesArray['values'] as $resourceName => $resourceValue) {
      $resourceValue['option_group_id'] = $currentOptionGroup['id'];
      $optionValue = CRM_Contactsegment_Utils::createOptionValue($resourceValue);
      if ($optionValue != FALSE) {
        foreach ($optionValue['values'] as $optionValueId => $optionValue) {
          $optionValues[$optionValueId] = $optionValue;
        }
      }
    }
    $currentOptionGroup['values'] = $optionValues;
    $this->_roleOptionGroup = $currentOptionGroup;
  }

  /**
   * Method to read json resources file
   *
   * @param $fileName
   * @return array|mixed
   * @throws Exception when file not found
   */
  private function getJsonResourcesArray($fileName) {
    $return = array();
    if (!empty($fileName)) {
      $jsonFile = $this->_resourcesPath.$fileName;
      if (!file_exists($jsonFile)) {
        throw new Exception("Could not load ".$fileName." required for extension org.civicoop.contactsegment,
        contact your system administrator");
      }
      $jsonData = file_get_contents($jsonFile);
      $return = json_decode($jsonData, true);
    }
    return $return;
  }

  /**
   * Method to return singleton object
   *
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Contactsegment_Config();
    }
    return self::$_singleton;
  }
}