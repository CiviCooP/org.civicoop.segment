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
    $preContactSegment = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a contact segment', 9003);
    }
    $contactSegment = new CRM_Contactsegment_BAO_ContactSegment();
    $op = "create";
    if (isset($params['id'])) {
      $contactSegment->id = $params['id'];
      // pre hook if edit
      $op = "edit";
      self::storeValues($contactSegment, $preContactSegment);
      CRM_Utils_Hook::pre($op, 'ContactSegment', $contactSegment->id, $preContactSegment);
      $contactSegment->find(true);
    } else {
      $params['is_active'] = 1;
    }
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $contactSegment->$paramKey = $paramValue;
      }
    }
    if (!$contactSegment->start_date) {
      $contactSegment->start_date = date("Ymd");
    }
    $contactSegment->processEndDate($params);
    $contactSegment->save();
    // post hook
    CRM_Utils_Hook::post($op, 'ContactSegment', $contactSegment->id, $contactSegment);
    self::storeValues($contactSegment, $result);
    // function to add or update parent if child
    self::processParentChild($contactSegment);
    return $result;
  }

  /**
   * Method to check if parent needs to be added or updated:
   * - if child contact segment is added then 3 cases are possible:
   *   - parent contact segment not there, has to be added
   *   - parent contact segment there with end date before end date child, set end date to child end date and is_active
   *   - parent contact segment there with later or same end date, do nothing
   *
   * @param $contactSegment
   * @access private
   * @static
   */
  private static function processParentChild($contactSegment) {
    $segmentParent = civicrm_api3('Segment', 'Getvalue',
      array('id' => $contactSegment->segment_id, 'return' => 'parent_id'));
    if ($segmentParent) {
      $countParentParams = array(
        'contact_id' => $contactSegment->contact_id,
        'role_value' => $contactSegment->role_value,
        'segment_id' => $segmentParent);
      $countParentContactSegment = civicrm_api3('ContactSegment', 'Getcount', $countParentParams);
      if ($countParentContactSegment == 0) {
        self::addParent($contactSegment, $segmentParent);
      } else {
        self::updateParent($contactSegment, $segmentParent);
      }
    } else {
      self::updateChildren($contactSegment);
    }
  }

  /**
   * Method to set end date for child contact segments if end date is set for parent
   *
   * @param $contactSegment
   * @access private
   * @static
   */
  private static function updateChildren($contactSegment) {
    $childrenSelect = 'SELECT id FROM civicrm_contact_segment
      WHERE contact_id = %1 AND role_value = %2 AND segment_id IN(SELECT id FROM civicrm_segment WHERE parent_id = %3)';
    $selectParams = array(
      1 => array($contactSegment->contact_id, 'Integer'),
      2 => array($contactSegment->role_value, 'String'),
      3 => array($contactSegment->segment_id, 'Integer'));
    $daoChildren = CRM_Core_DAO::executeQuery($childrenSelect, $selectParams);
    while ($daoChildren->fetch()) {
      $childUpdate = 'UPDATE civicrm_contact_segment SET end_date = %1, is_active = %2 WHERE id = %3';
      $updateParams = array(
        1 => array($contactSegment->end_date, 'String'),
        2 => array($contactSegment->is_active, 'Integer'),
        3 => array($daoChildren->id, 'Integer'));
      CRM_Core_DAO::executeQuery($childUpdate, $updateParams);
    }
  }

  /**
   * Method to update parent contact segment if required
   *
   * @param $contactSegment
   * @param $segmentParent
   * @access private
   * @static
   */
  private static function updateParent($contactSegment, $segmentParent) {
    $parentContactSegment = civicrm_api3('ContactSegment', 'Getsingle',
      array(
        'contact_id' => $contactSegment->contact_id,
        'segment_id' => $segmentParent,
        'role_value' => $contactSegment->role_value));
    if (!$contactSegment->end_date) {
      $parentContactSegment['end_date'] = '';
      $parentContactSegment['is_active'] = 1;
    } else {
      $childEndDate = new DateTime($contactSegment->end_date);
      $parentEndDate = new DateTime($parentContactSegment['end_date']);
      if ($parentEndDate < $childEndDate) {
        $parentContactSegment['end_date'] = $childEndDate->format('Ymd');
        $parentContactSegment['is_active'] = $contactSegment->is_active;
      }
    }
    self::add($parentContactSegment);
  }

  /**
   * Method to add parent contact segment if required
   *
   * @param $contactSegment
   * @param $segmentParent
   * @access private
   * @static
   */
  private static function addParent($contactSegment, $segmentParent) {
    $parentSegmentParams = array(
      'contact_id' => $contactSegment->contact_id,
      'segment_id' => $segmentParent,
      'role_value' => $contactSegment->role_value,
      'start_date' => $contactSegment->start_date,
      'end_date' => $contactSegment->end_date,
      'is_active' => $contactSegment->is_active);
    self::add($parentSegmentParams);
  }

  /**
   * Method to process end date and set is active accordingly
   *
   * @param array $params
   * $access private
   */
  private function processEndDate($params) {
    if (!$params['end_date']) {
      $this->end_date = '';
      $this->is_active = 1;
    } else {
      $endDate = new DateTime($this->end_date);
      $nowDate = new DateTime();
      if ($endDate <= $nowDate) {
        $this->is_active = 0;
      }
    }
  }

  /**
   * Implementation of hook civicrm_tabs to add a tab for contact segments
   *
   * @param array $tabs
   * @param int $contactId
   * @access public
   * @static
   */
  public static function tabs(&$tabs, $contactId) {
    $weight = 0;
    foreach ($tabs as $tabId => $tab) {
      if ($tab['id'] == 'group') {
        $weight = $tab['weight']--;
      }
    }
    $count = civicrm_api3('ContactSegment', 'Getcount', array('contact_id' => $contactId, 'is_active' => 1));
    $segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $title = $segmentSetting['parent_label']." and ".$segmentSetting['child_label'];
    $tabs[] = array(
      'id'    => 'contactSegments',
      'url'       => CRM_Utils_System::url('civicrm/contactsegmentlist', 'snippet=1&cid='.$contactId),
      'title'     => $title,
      'weight'    => $weight,
      'count'     => $count);
  }

  /**
   * Method to get a contact with a given role for a given segment on a specific date
   * (assumption is that there is only 1 contact. If there are more, method will return the first one found)
   *
   * @param string $role
   * @param int $segmentId
   * @param date $testDate
   * @return bool|array
   * @access public
   * @static
   */
  public static function getRoleContactActiveOnDate($role, $segmentId, $testDate) {
    if (empty($role) || empty($segmentId) || empty($testDate)) {
      return FALSE;
    }
    $testDate = new DateTime($testDate);
    $config = CRM_Contactsegment_Config::singleton();
    if (!in_array($role, $config->getRoleOptionGroup())) {
      return FALSE;
    }
    $params = array('segment_id' => $segmentId, 'role_value' => $role);
    $roleContactSegments = civicrm_api3('ContactSemgent', 'Get', $params);
    foreach ($roleContactSegments['values'] as $contactSegment) {
      $startDate = new DateTime($contactSegment['start_date']);
      if ($testDate >= $startDate) {
        if (!$contactSegment['end_date']) {
          return $contactSegment['contact_id'];
        } else {
          $endDate = new DateTime($contactSegment['end_date']);
          if ($endDate >= $testDate) {
            return $contactSegment['contact_id'];
          }
        }
      }
    }
    return FALSE;
  }
}
