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
  protected $roleTypeOptionGroup = array();

  /**
   * Constructor method
   */
  public function __construct() {
    // TODO : implement
  }

  /**
   * Method to get the option group id of the role types
   *
   * @return int
   * @access public
   */
  public function getRoleTypeOptionGroupId() {
    return $this->roleTypeOptionGroup['id'];
  }

  /**
   * Method to get the option group (with option values) of the role types
   *
   * @return array
   * @access public
   */
  public function getRoleTypeOptionGroup() {
    return $this->roleTypeOptionGroup();
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