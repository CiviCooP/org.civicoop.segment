<?php
/**
 * Class BAO civicrm_contact_segment
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */
class CRM_Contactsegment_BAO_ContactSegment extends CRM_Contactsegment_DAO_ContactSegment {

  const NO_STATE_CHANGE = 0;
  const ACTIVATING_STATE = 1;
  const DEACTIVATING_STATE = -1;

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
    // check if there is already a record for combination of role_value, contact_id and segment. If so, use that id to edit
    $contactSegment->checkAlreadyExists($params);
    $id = null;
    if (isset($params['id'])) {
      $op = "edit";
      $id = $params['id'];
    }

    CRM_Utils_Hook::pre($op, 'ContactSegment', $id, $params);

    // Update the is_active status based on start_date or end_date
    $now = new DateTime();
    $startDate = false;
    $endDate = false;
    if (!empty($params['start_date'])) {
      $startDate = new DateTime($params['start_date']);
    }
    if (!empty($params['end_date'])) {
      $endDate = new DateTime($params['end_date']);
    }
    if ($startDate && $startDate > $now) {
      $params['is_active'] = '0';
    } elseif ($endDate && $endDate < $now) {
      $params['is_active'] = '0';
    } else {
      $params['is_active'] = '1';
    }

    $state = self::NO_STATE_CHANGE;
    if (!empty($id)) {
      // Check the current state of the contact segment
      $currentIsActive = civicrm_api3('ContactSegment', 'getvalue', array('id' => $id, 'return' => 'is_active'));
      if ($currentIsActive && !$params['is_active']) {
        $state = self::DEACTIVATING_STATE;
      } elseif (!$currentIsActive && $params['is_active']) {
        $state = self::ACTIVATING_STATE;
      }
    } elseif ($params['is_active'] == '0') {
      $state = self::DEACTIVATING_STATE;
    } elseif ($params['is_active'] == '1') {
      $state = self::ACTIVATING_STATE;
    }

    // Check whether we are dealing with a parent of a child
    // First find the linked segment and based on that decided on the existence of
    // the parent id whether we are updating a child or a parent.
    $segment = new CRM_Contactsegment_BAO_Segment();
    $segment->id = $params['segment_id'];
    if (!$segment->find(true)) {
      throw new Exception('Could not find the linked segment');
    }

    if (!empty($segment->parent_id) && !empty($params['is_active'])) {
      // This is a child contact segment which is activated

      // Check whether this valid
      // 1. An active parent contact segment is found and period is in period of the parent
      // 2 Or when no parents are found at all (then we have a floating child and those are allowed)
      //
      // Also update an empty start date or an empty end date when the parent
      // contact segment has non empty start date or a non empty end date.
      $parentContactSegmentCount = civicrm_api3('ContactSegment', 'getcount', array('contact_id' => $params['contact_id'], 'segment_id' => $segment->parent_id));
      $isFloating = false;
      $periodIsValid = false;
      if ($parentContactSegmentCount == 0) {
        $isFloating = true;
        $insertParentSql = "INSERT INTO civicrm_contact_segment (contact_id, segment_id, start_date, end_date, is_active, role_value) VALUES (%1, %2, %3, %4, '1', %5);";
        $insertParentParams[1] = array($params['contact_id'], 'Integer');
        $insertParentParams[2] = array($segment->parent_id, 'Integer');
        $insertParentParams[3] = array(empty($startDate) ? null : $startDate->format('Ymd'), 'Date');
        $insertParentParams[4] = array(empty($endDate) ? null : $endDate->format('Ymd'), 'Date');
        $insertParentParams[5] = array($params['role_value'], 'String');
        CRM_Core_DAO::executeQuery($insertParentSql, $insertParentParams);
      } else {
        $activeContactSegments = civicrm_api3('ContactSegment', 'get', array(
          'contact_id' => $params['contact_id'],
          'segment_id' => $segment->parent_id,
          'is_active' => '1'
        ));
        foreach ($activeContactSegments['values'] as $activeContactSegment) {
          // Period is valid when no start_date and no end_date or set
          // Or when end date of the parent is empty and the start date is after the start date of the parent
          // Or when start date of the parent is empty and the end date is before the end date of the parent
          // Or when start date is between start date of the parent and end date of the parent
          if (empty($activeContactSegment['start_date']) && empty($activeContactSegment['end_date'])) {
            $periodIsValid = TRUE;
          }
          elseif (!empty($activeContactSegment['start_date']) && empty($activeContactSegment['end_date'])) {
            $activeContactSegmentStartDate = new DateTime($activeContactSegment['start_date']);
            if ($startDate && $startDate >= $activeContactSegmentStartDate) {
              $periodIsValid = TRUE;
            }
            elseif (empty($params['start_date'])) {
              $params['start_date'] = $activeContactSegmentStartDate->format('Ymd');
              $periodIsValid = TRUE;
            }
          }
          elseif (empty($activeContactSegment['start_date']) && !empty($activeContactSegment['end_date'])) {
            $activeContactSegmentEndDate = new DateTime($activeContactSegment['end_date']);
            if ($endDate && $endDate >= $activeContactSegmentEndDate) {
              $periodIsValid = TRUE;
            }
            elseif (empty($params['end_date'])) {
              $params['end_date'] = $activeContactSegmentEndDate->format('Ymd');
              $periodIsValid = TRUE;
            }
          }
          elseif (!empty($activeContactSegment['start_date']) && !empty($activeContactSegment['end_date'])) {
            $activeContactSegmentStartDate = new DateTime($activeContactSegment['start_date']);
            $activeContactSegmentEndDate = new DateTime($activeContactSegment['end_date']);
            if (empty($params['start_date'])) {
              $params['start_date'] = $activeContactSegmentStartDate->format('Ymd');
            }
            if (empty($params['end_date'])) {
              $params['end_date'] = $activeContactSegmentEndDate->format('Ymd');
            }
            $startDate = new DateTime($params['start_date']);
            $endDate = new DateTime($params['end_date']);

            if ($startDate >= $activeContactSegmentStartDate && $startDate <= $activeContactSegmentEndDate && $endDate >= $activeContactSegmentStartDate && $endDate <= $activeContactSegmentEndDate) {
              $periodIsValid = TRUE;
            }
          }

          if ($periodIsValid) {
            break;
          }
        }
      }

      if (!$isFloating && !$periodIsValid) {
        throw new CRM_Contactsegment_Exception_InvalidChildPeriod('Invalid period for child contact segment. The period does not fit into an active parent contact segment');
      }
    } elseif (empty($segment->parent_id) && $state == self::DEACTIVATING_STATE) {
      // This is a parent and is deactivated also deactivate the children
      // We do this with an SQL statement and not with an API call because the SQL
      // statement is better for performance. Other than that we don't have any reasons to
      // do this with SQL.
      $deactiveChildContactSegmentSql = "UPDATE `civicrm_contact_segment` SET `is_active` = '0' WHERE `contact_id` = %1 AND `is_active` = '1' AND `segment_id` IN (SELECT `id` FROM `civicrm_segment` WHERE `parent_id` = %2)";
      $deactiveChildContactSegmentParams[1] = array($params['contact_id'], 'Integer');
      $deactiveChildContactSegmentParams[2] = array($params['segment_id'], 'Integer');
      CRM_Core_DAO::executeQuery($deactiveChildContactSegmentSql, $deactiveChildContactSegmentParams);
    } elseif (empty($segment->parent_id) && $state == self::ACTIVATING_STATE) {
      // This is a parent and is activated also the children
      // We do this with an SQL statement and not with an API call because the SQL
      // statement is better for performance. Other than that we don't have any reasons to
      // do this with SQL.

      // Select first all the children then keep track of which child we have updated and only activate
      // one segment (so if a child segment exists multiple times in the past segments then only update one
      // of the rows).
      $alreadyActivatedChildren = array();
      $activateChildContactSegmentSelectSql = "SELECT * FROM `civicrm_contact_segment` WHERE `contact_id` = %1 AND `is_active` = '0' AND `segment_id` IN (SELECT `id` FROM `civicrm_segment` WHERE `parent_id` = %2)";
      $activateChildContactSegmentSelectSqlParams[1] = array($params['contact_id'], 'Integer');
      $activateChildContactSegmentSelectSqlParams[2] = array($params['segment_id'], 'Integer');
      $activateChildContactSegmentSelectDao = CRM_Core_DAO::executeQuery($activateChildContactSegmentSelectSql, $activateChildContactSegmentSelectSqlParams);
      while ($activateChildContactSegmentSelectDao->fetch()) {
        if (!in_array($activateChildContactSegmentSelectDao->segment_id, $alreadyActivatedChildren)) {
          $activateChildContactSegmentSql = "UPDATE `civicrm_contact_segment` SET `is_active` = '1', start_date = %1, end_date = %2 WHERE id = %3";
          $activateChildContactSegmentSqlParams[1] = array(empty($startDate) ? null : $startDate->format('Ymd'), 'Date');
          $activateChildContactSegmentSqlParams[2] = array(empty($endDate) ? null : $endDate->format('Ymd'), 'Date');
          $activateChildContactSegmentSqlParams[3] = array($activateChildContactSegmentSelectDao->id, 'Integer');
          CRM_Core_DAO::executeQuery($activateChildContactSegmentSql, $activateChildContactSegmentSqlParams);

          $alreadyActivatedChildren[] = $activateChildContactSegmentSelectDao->segment_id;
        }
      }
    }

    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $contactSegment->$paramKey = $paramValue;
      }
    }
    $contactSegment->save();
    CRM_Utils_Hook::post($op, 'ContactSegment', $contactSegment->id, $contactSegment);
    self::storeValues($contactSegment, $result);
    return $result;
  }

  /**
   * Method to use the id if there is already a contactsegment for the values that are about to be created
   * @param $params
   */
  private function checkAlreadyExists(&$params) {
    $result = self::getValues($params);
    if (!empty($result)) {
      foreach ($result as $id => $values) {
        $params['id'] = $id;
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
    $roleOptionGroup = $config->getRoleOptionGroup();
    $roleFound = FALSE;
    foreach ($roleOptionGroup['values'] as $roleOptionValue) {
      if ($roleOptionValue['label'] == $role) {
        $roleFound = TRUE;
      }
    }
    if ($roleFound) {
      $params = array('segment_id' => $segmentId, 'role_value' => $role);
      $roleContactSegments = civicrm_api3('ContactSegment', 'Get', $params);
      foreach ($roleContactSegments['values'] as $contactSegment) {
        $startDate = new DateTime($contactSegment['start_date']);
        if ($testDate >= $startDate) {
          if (empty($contactSegment['end_date'])) {
            return $contactSegment['contact_id'];
          } else {
            $endDate = new DateTime($contactSegment['end_date']);
            if ($endDate >= $testDate) {
              return $contactSegment['contact_id'];
            }
          }
        }
      }
    }
    return FALSE;
  }
}
