<?php

/**
 * ContactSegment.Disable API
 * Will disable all active contact segments where end date has passed
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_segment_disable($params) {
  $query = "UPDATE civicrm_contact_segment SET is_active = %1 WHERE end_date < CURDATE() AND is_active = %2";
  $queryParams = array(
    1 => array(0, "Integer"),
    2 => array(1, "Integer"));
  CRM_Core_DAO::executeQuery($query, $queryParams);
  return civicrm_api3_create_success(array(), $params, 'ContactSegment', 'Disable');
}

