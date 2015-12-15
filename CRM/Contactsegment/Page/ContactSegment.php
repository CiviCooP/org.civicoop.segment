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
    $contactSegmentParams = array();
    $activeContactSegments = array();
    $pastContactSegments = array();
    if ($this->_contactId) {
      $contactSegmentParams = array('contact_id' => $this->_contactId);
    }
    try {
      $fetchedContactSegment = civicrm_api3('ContactSegment', 'Get', $contactSegmentParams);
      foreach ($fetchedContactSegment['values'] as $contactSegmentId => $contactSegment) {
        $this->buildContactSegmentRow($contactSegment, $activeContactSegments, $pastContactSegments);
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    $this->assign('activeContactSegments', $activeContactSegments);
    $this->assign('pastContactSegments', $pastContactSegments);
  }

  /**
   * Method to build a contact segment row, active or past
   *
   * @param array $contactSegment
   * @param array $activeContactSegments
   * @param array $pastContactSegments
   * @access private
   */
  private function buildContactSegmentRow($contactSegment, &$activeContactSegments, &$pastContactSegments) {
    if (!empty($contactSegment)) {
      $displaySegment = array();
      $segment = civicrm_api3('Segment', 'Getsingle', array('id' => $contactSegment['segment_id']));
      if ($segment) {
        $displaySegment['label'] = $segment['label'];
        if (!$segment['parent_id']) {
          $displaySegment['type'] = $this->_segmentSetting['parent_label'];
        } else {
          $displaySegment['type'] = $this->_segmentSetting['child_label'];
        }
      }
      $displaySegment['start_date'] = $contactSegment['start_date'];
      $displaySegment['end_date'] = $contactSegment['end_date'];
      $displaySegment['role'] = CRM_Contactsegment_Utils::getRoleLabel($contactSegment['role_value']);
      $displaySegment['actions'] = $this->buildRowActions($contactSegment);
      if ($contactSegment['is_active'] = 1) {
        $activeContactSegments[$contactSegment['id']] = $displaySegment;
      } else {
        $pastContactSegments[$contactSegment['id']] = $displaySegment;
      }
    }
  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param array $contactSegment
   * @return array $pageActions
   * @access private
   */
  protected function buildRowActions($contactSegment) {
    $pageActions = array();
    $editUrl = CRM_Utils_System::url('civicrm/contactsegment', 'reset=1&action=update&cid='.$this->_contactId.'&csid='.$contactSegment['id'], true);
    $pageActions[] = '<a class="action-item" title="Edit" href="'.$editUrl.'">Edit</a>';
    $endUrl = CRM_Utils_System::url('civicrm/contactsegment', 'reset=1&action=close'.$this->_contactId.'&csid='.$contactSegment['id'], true);
    if ($contactSegment['is_active'] == 1) {
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
  }
}
