<?php
require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_ContactSegment extends CRM_Core_Form {

  protected $_contactId = NULL;
  protected $_contactSegmentId = NULL;
  protected $_parentLabel = NULL;
  protected $_childLabel = NULL;
  protected $_contactSegment = array();

  function buildQuickForm() {
    $this->addFormElements();
    parent::buildQuickForm();
  }

  /**
   * Method to get the segment labels
   *
   * @access private
   */
  private function getSegmentLabels() {
    $segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $this->_parentLabel = $segmentSetting['parent_label'];
    $this->_childLabel = $segmentSetting['child_label'];
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_contactSegmentId = CRM_Utils_Request::retrieve('csid', 'Integer');
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Integer');
    $this->getSegmentLabels();
    if ($this->_action != CRM_Core_Action::ADD && $this->_contactSegmentId) {
      $this->_contactSegment = civicrm_api3('ContactSegment', 'Getsingle', array('id' => $this->_contactSegmentId));
    }
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->deleteContactSegmentAndReturn();
    }
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionLabel = "Add";
        $headerLabel = $this->_parentLabel . " or " . $this->_childLabel;
        break;
      case CRM_Core_Action::UPDATE:
        $actionLabel = "Edit";
        $headerLabel = civicrm_api3('Segment', 'Getvalue',
          array('id' => $this->_contactSegment['segment_id'], 'return' => 'label'));
        break;
      case CRM_Core_Action::VIEW:
        $actionLabel = "View";
        $headerLabel = civicrm_api3('Segment', 'Getvalue',
          array('id' => $this->_contactSegment['segment_id'], 'return' => 'label'));
        break;
    }
    CRM_Utils_System::setTitle($actionLabel." ".$headerLabel);
    $this->assign('actionLabel', $actionLabel);
    $this->assign('headerLabel', $headerLabel);
  }

  /**
   * Overridden parent method to process form (calls parent method too)
   *
   * @access public
   */
  function postProcess() {
    $values = $this->exportValues();
    if (!$values['contact_id']) {
      $this->_contactId = $values['contact_id'];
    }
    if (!$values['contact_segment_id']) {
      $this->_contactSegmentId = $values['contact_segment_id'];
    }
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveContactSegment($values);
    }
    parent::postProcess();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['contact_id'] = $this->_contactId;
    $defaults['contact_segment_id'] = $this->_contactSegmentId;
    if ($this->_action == CRM_Core_Action::ADD) {
      list($defaults['start_date']) = CRM_Utils_Date::setDateDefaults(date('d-m-Y'));
    }
    return $defaults;
  }

  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $roleList = CRM_Contactsegment_Utils::getRoleList();
    $parentList = CRM_Contactsegment_Utils::getParentList();
    $this->add('hidden', 'contact_id');
    $this->add('hidden', 'contact_segment_id');
    $this->add('select', 'contact_segment_role', ts('Role'), $roleList, true);
    $this->add('select', 'segment_parent', ts($this->_parentLabel), $parentList, true);
    $this->add('select', 'segment_child', ts($this->_childLabel), array());
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Function to save the contact segment
   *
   * @param $formValues
   * @access protected
   */
  protected function saveContactSegment($formValues) {
    $params = array();
    if ($formValues['contact_segment_id']) {
      $params['id'] = $formValues['contact_segment_id'];
    }
    $params['contact_id'] = $formValues['contact_id'];
    $params['role_value'] = $formValues['contact_segment_role'];
    if ($formValues['segment_child']) {
      $params['segment_id'] = $formValues['segment_child'];
    } else {
      $params['segment_id'] = $formValues['segment_parent'];
    }
    $segmentLabel = civicrm_api3('Segment', 'Getvalue', array('id' => $params['segment_id'], 'return' => 'label'));
    if ($formValues['start_date']) {
      $params['start_date'] = $formValues['start_date'];
    }
    if ($formValues['end_date']) {
      $params['end_date'] = $formValues['end_date'];
    }
    $this->_contactSegment = civicrm_api3('ContactSegment', 'Create', $params);
    $session = CRM_Core_Session::singleton();
    $session->setStatus("Contact linked to ".$segmentLabel." as ".$formValues['contact_segment_role'],
      "Contact Linked to ".$segmentLabel, "success");
  }

  /**
   * Method to delete contact segment
   *
   */
  protected function deleteContactSegmentAndReturn() {
    // TODO contact segment can only be deleted if contact is not active in one of his roles on a case
    // TODO function to check this perhaps before delete option is made available
    $displayName = civicrm_api3('Contact', 'Getvalue',
      array('id' => $this->_contactSegment['contact_id'], 'return' => 'display_name'));
    $segment = civicrm_api3('Segment', 'Getsingle', array('id' => $this->_contactSegment['segment_id']));
    if (!$segment['parent_id']) {
      $statusMessage = $this->_parentLabel." removed from contact ".$displayName;
      $statusTitle = $this->_parentLabel." removed";
    } else {
      $statusMessage = $this->_childLabel." removed from contact ".$displayName;
      $statusTitle = $this->_childLabel." removed";
    }
    civicrm_api3('ContactSegment', 'Delete', array('id' => $this->_contactSegmentId));
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }
}
