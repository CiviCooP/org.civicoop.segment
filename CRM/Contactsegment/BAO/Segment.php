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
    if (!$segment->name && $segment->label) {
      $segment->name = CRM_Contactsegment_Utils::generateNameFromLabel($segment->label);
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

  /**
   * Method to get the label of a segment
   *
   * @param int $segmentId
   * @return string
   * @access public
   * @static
   */
  public static function getSegmentLabelWithId($segmentId) {
    if (empty($segmentId)) {
      return "";
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->id = $segmentId;
    if ($segment->find(true)) {
      return $segment->label;
    } else {
      return "";
    }
  }

  /**
   * Method to get the parent of a segment
   *
   * @param int $segmentId
   * @return string
   * @access public
   * @static
   */
  public static function getSegmentParentIdWithId($segmentId) {
    if (empty($segmentId)) {
      return FALSE;
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->id = $segmentId;
    if ($segment->find(true)) {
      return $segment->parent_id;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get single segment with id
   *
   * @param int $segmentId
   * @return array
   * @access public
   * @static
   */
  public static function getSingleSegmentWIthId($segmentId) {
    $result = array();
    if (!empty($segmentId)) {
      $segment = new CRM_Contactsegment_BAO_Segment();
      $segment->id = $segmentId;
      if ($segment->find(true)) {
        self::storeValues($segment, $result);
      }
    }
    return $result;
  }

  /**
   * Method to get only parent segments
   *
   * @return array
   * @access public
   * @static
   */
  public static function getParentSegments() {
    $result = array();
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->find();
    while ($segment->fetch()) {
      if (!$segment->parent_id) {
        $row = array();
        self::storeValues($segment, $row);
        $result[$row['id']] = $row;
      }
    }
    return $result;
  }
}

