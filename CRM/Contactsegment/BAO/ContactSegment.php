<?php
/**
 * Class BAO civicrm_contact_segment
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */
class CRM_Contactsegment_BAO_ContactSegment extends CRM_Contactsegment_DAO_ContactSegment {

  /**
   * Function to get values
   * 
   * @param array $params name/value pairs with field names/values
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $contactSegment = new CRM_Contactsegment_BAO_ContactSegment();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $contactSegment->$paramKey = $paramValue;
        }
      }
    }
    $contactSegment->find();
    while ($contactSegment->fetch()) {
      $row = array();
      self::storeValues($contactSegment, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update contact segment
   * 
   * @param array $params
   * @return array $result
   * @throws Exception when params empty
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a contact segment');
    }
    $contactSegment = new CRM_Contactsegment_BAO_ContactSegment();
    if (isset($params['id'])) {
      $contactSegment->id = $params['id'];
      $contactSegment->find(true);
    }
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $contactSegment->$paramKey = $paramValue;
      }
    }
    $contactSegment->save();
    self::storeValues($contactSegment, $result);
    return $result;
  }
}
