<?php
/**
 * Page ContactSegment to list all Segments for contact
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 28 November 2015
 * @license AGPL-3.0
 */
require_once 'CRM/Core/Page.php';

class CRM_Contactsegment_Page_ContactSegment extends CRM_Core_Page {
  protected $_segmentSetting = array();
  protected $_contactId = NULL;

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->getContactSegments();
    parent::run();
  }

  /**
   * Function to get the active and past contact segments
   *
   * @access private
   */
  private function getContactSegments() {
    $this->assign('activeContactSegments', $this->getParentsWithChildren(1));
    $this->assign('pastContactSegments', $this->getParentsWithChildren(0));
  }

  /**
   * Method to get parents and children for each parent
   *
   * @param int $isActive
   * @return array $rows
   * @access private
   */
  private function getParentsWithChildren($isActive) {
    $selectedChildren = array();
    $rows = array();
    $queryParents = "SELECT cs.id AS contact_segment_id, segment_id, role_value, start_date, end_date, is_active, label, parent_id
      FROM civicrm_contact_segment cs JOIN civicrm_segment sgmnt ON cs.segment_id = sgmnt.id
      WHERE contact_id = %1 AND parent_id IS NULL AND is_active = %2";
    $paramsParents = array(
      1 => array($this->_contactId, 'Integer'),
      2 => array($isActive, 'Integer'));

    $daoParents = CRM_Core_DAO::executeQuery($queryParents, $paramsParents);
    while ($daoParents->fetch()) {
      $rows[$daoParents->contact_segment_id] = $this->buildContactSegmentRow($daoParents);
      $queryChildren = "SELECT cs.id AS contact_segment_id, segment_id, role_value, start_date, end_date,
        is_active, label, parent_id FROM civicrm_contact_segment cs
        JOIN civicrm_segment sgmnt ON cs.segment_id = sgmnt.id
        WHERE contact_id = %1 AND parent_id = %2 AND role_value = %3 AND is_active = %4";
      $paramsChildren = array(
        1 => array($this->_contactId, 'Integer'),
        2 => array($daoParents->segment_id, 'Integer'),
        3 => array($daoParents->role_value, 'String'),
        4 => array($isActive, 'Integer'));
      $daoChildren = CRM_Core_DAO::executeQuery($queryChildren, $paramsChildren);
      while ($daoChildren->fetch()) {
        $rows[$daoChildren->contact_segment_id] = $this->buildContactSegmentRow($daoChildren);
        $selectedChildren[] = $daoChildren->contact_segment_id;
      }
    }
    // for past there could be 'floating' children
    if ($isActive == 0) {
      $this->addFloatingChildren($selectedChildren);
    }
    return $rows;
  }
  /**
   * Method to add floating children
   *
   * @param array $selectedChildren
   * @access private
   */
  private function addFloatingChildren($selectedChildren) {
    $floatingRows = array();
    $query = "SELECT cs.id AS contact_segment_id, segment_id, role_value, start_date, end_date,
      is_active, label, parent_id FROM civicrm_contact_segment cs
      JOIN civicrm_segment sgmnt ON cs.segment_id = sgmnt.id
      WHERE contact_id = %1 AND is_active = %2 AND parent_id IS NOT NULL";
    $params = array(
      1 => array($this->_contactId, 'Integer'),
      2 => array(0, 'Integer')
    );
    if (!empty($selectedChildren)) {
      $count = 2;
      foreach ($selectedChildren as $selectedChild) {
        $count++;
        $query .= " AND cs.id != %".$count;
        $params[$count] = array($selectedChild, 'Integer');
      }
    }
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $floatingRows[$dao->contact_segment_id] = $this->buildContactSegmentRow($dao);
    }
    $this->assign('floatingContactSegments', $floatingRows);
  }

  /**
   * Method to build a contact segment row, active or past
   *
   * @param object $dao
   * @return array $row
   * @access private
   */
  private function buildContactSegmentRow($dao) {
    $row = array();
    if (empty($dao->parent_id)) {
      $row['type'] = $this->_segmentSetting['parent_label'];
      $row['label'] = $dao->label;
    } else {
      $row['type'] = $this->_segmentSetting['child_label'];
      $row['label'] = ' - '.$dao->label;
    }
    $row['start_date'] = $dao->start_date;
    if (isset($dao->end_date) && !empty($dao->end_date)) {
      $row['end_date'] = $dao->end_date;
    }
    $row['role'] = CRM_Contactsegment_Utils::getRoleLabel($dao->role_value);
    $row['actions'] = $this->buildRowActions($dao);
    return $row;
  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param object $dao
   * @return array $pageActions
   * @access private
   */
  protected function buildRowActions($dao) {
    $pageActions = array();
    $editUrl = CRM_Utils_System::url('civicrm/contactsegment', 'reset=1&action=update&cid='.$this->_contactId.'&csid='.$dao->contact_segment_id, true);
    $pageActions[] = '<a class="action-item" title="Edit" href="'.$editUrl.'">Edit</a>';
    $endUrl = CRM_Utils_System::url('civicrm/contactsegment', 'reset=1&action=close&cid='.$this->_contactId.'&csid='.$dao->contact_segment_id, true);
    if ($dao->is_active == 1) {
      $pageActions[] = '<a class="action-item" title="Close" href="' . $endUrl . '">End</a>';
    }
    return $pageActions;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    $this->_segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Integer');
    if (empty($this->_contactId)) {
      $displayName = "";
      $addUrl = CRM_Utils_System::url('civicrm/contactsegment', 'action=add', true);
    } else {
      $displayName = civicrm_api3('Contact', 'Getvalue', array('id' => $this->_contactId, 'return' => 'display_name'));
      $addUrl = CRM_Utils_System::url('civicrm/contactsegment', 'action=add&cid='.$this->_contactId, true);
    }
    $this->assign('parentSegmentLabel', $this->_segmentSetting['parent_label']);
    $this->assign('childSegmentLabel', $this->_segmentSetting['child_label']);
    CRM_Utils_System::setTitle(ts($this->_segmentSetting['parent_label']. "s and " . $this->_segmentSetting['child_label']
      . "s for ".$displayName));
    $this->assign('addUrl', $addUrl);
    $session = CRM_Core_Session::singleton();
    $contactSegmentUrl= CRM_Utils_System::url('civicrm/contact/view', 'action=browse&selectedChild=contactSegments', true);
    $session->pushUserContext($contactSegmentUrl);
  }
}
