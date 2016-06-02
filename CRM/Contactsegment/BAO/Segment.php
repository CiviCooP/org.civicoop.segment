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
      if (!isset($row['parent_id']) || empty($row['parent_id'])) {
        $row['parent_id'] = NULL;
      }
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
    $preSegment = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a segment', 9003);
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    if (isset($params['id'])) {
      $segment->id = $params['id'];
      // pre hook if edit
      $op = "edit";
      self::storeValues($segment, $preSegment);
      CRM_Utils_Hook::pre($op, 'Segment', $segment->id, $preSegment);
      $segment->find(true);
    } else {
      $op = "create";
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

    if (!$segment->is_active) {
      CRM_Contactsegment_BAO_Segment::setInactive($segment->id);
    }

    // post hook
    CRM_Utils_Hook::post($op, 'Segment', $segment->id, $segment);
    self::storeValues($segment, $result);
    return $result;
  }

  /**
   * Function to set the inactive flag of a segment.
   *
   * This function will also set the child segments to inactive and will
   * set the end date for contactsegments.
   *
   * @param $segmentId
   */
  public static function setInactive($segmentId) {
    // Set active contact segments to inactive with an end date to today.
    $today = new DateTime();
    $contact_segments = civicrm_api3('ContactSegment', 'get', array('segment_id' => $segmentId, 'is_active' => '1'));
    foreach($contact_segments['values'] as $contact_segment) {
      $contact_segment['is_active'] = 0;
      $contact_segment['end_date'] = $today->format('Ymd');
      civicrm_api3('ContactSegment', 'create', $contact_segment);
    }

    // Set child segment to inactive
    $child_segments = civicrm_api3('Segment', 'get', array('parent_id' => $segmentId));
    foreach($child_segments['values'] as $child_segment) {
      $child_segment['is_active'] = false;
      civicrm_api3('Segment', 'create', $child_segment);
    }
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
    $preSegment = array();
    if (empty($segmentId)) {
      throw new Exception('segmentId can not be empty when attempting to delete one', 9004);
    }
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->id = $segmentId;
    self::storeValues($segment, $preSegment);
    CRM_Utils_Hook::pre('delete', 'Segment', $segment->id, $preSegment);
    $segment->delete();
    return TRUE;
  }

  /**
   * Method to build the table civicrm_segment_tree. This table holds double data but holds the id's in parent/child
   * sequence so the page can be built quickly
   *
   * @access public
   * @static
   */
  public static function buildSegmentTree() {
    CRM_Core_DAO::executeQuery("TRUNCATE TABLE civicrm_segment_tree");
    $daoParent = CRM_Core_DAO::executeQuery("SELECT id FROM civicrm_segment WHERE parent_id IS NULL ORDER BY label");
    while ($daoParent->fetch()) {
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_segment_tree SET id = %1", array(1 => array($daoParent->id, 'Integer')));
      $qryChild = "SELECT id FROM civicrm_segment WHERE parent_id = %1 ORDER BY label";
      $paramsChild = array(1 => array($daoParent->id, 'Integer'));
      $daoChild = CRM_Core_DAO::executeQuery($qryChild, $paramsChild);
      while ($daoChild->fetch()) {
        CRM_Core_DAO::executeQuery("INSERT INTO civicrm_segment_tree SET id = %1", array(1 => array($daoChild->id, 'Integer')));
      }
    }
  }
}

