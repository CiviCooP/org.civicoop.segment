<?php
/**
 * Class BAO civicrm_segment
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */
class CRM_Contactsegment_BAO_Segment extends CRM_Contactsegment_DAO_Segment {

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
    $segment = new CRM_Contactsegment_BAO_Segment();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $segment->$paramKey = $paramValue;
        }
      }
    }
    $segment->find();
    while ($segment->fetch()) {
      $row = array();
      self::storeValues($segment, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update segment
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
      throw new Exception('Params can not be empty when adding or updating a segment');
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    if (isset($params['id'])) {
      $segment->id = $params['id'];
      $segment->find(true);
    }
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $segment->$paramKey = $paramValue;
      }
    }
    $segment->save();
    self::storeValues($segment, $result);
    return $result;
  }

  /**
   * Function to delete segment (contact_segments will be deleted by MySQL CASCADE)
   * 
   * @param int $segmentId
   * @throws Exception when segmentId empty
   * @return boolean
   * @access public
   * @static
   */
  public static function deleteById($segmentId) {
    if (empty($segmentId)) {
      throw new Exception('segmentId can not be empty when attempting to delete one');
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->id = $segmentId;
    $segment->delete();
    return TRUE;
  }
}
