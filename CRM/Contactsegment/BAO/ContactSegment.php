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
    return $result;
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
}
