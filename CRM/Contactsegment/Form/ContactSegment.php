<?php
require_once 'CRM/Core/Form.php';

// TODO: validate against available roles per segment

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_ContactSegment extends CRM_Core_Form {

  protected $_contactId = NULL;
  protected $_contactSegmentId = NULL;
  protected $_parentSegmentId = NULL;
  protected $_parentLabel = NULL;
  protected $_childLabel = NULL;
  protected $_contactSegment = array();

  function buildQuickForm() {
    $this->addFormElements();
    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Contactsegment_Form_ContactSegment', 'validateRole'));
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
    if ($this->_action == CRM_Core_Action::CLOSE) {
      $this->closeContactSegmentAndReturn();
    }
    $actionLabel = "Add";
    if ($this->_action == CRM_Core_Action::UPDATE && $this->_contactSegmentId) {
      $this->_contactSegment = civicrm_api3('ContactSegment', 'Getsingle', array('id' => $this->_contactSegmentId));
      $this->_parentSegmentId = civicrm_api3('Segment', 'Getvalue',
        array('id' => $this->_contactSegment['segment_id'], 'return' => 'parent_id'));
      $actionLabel = "Edit";
    }
    $headerLabel = $this->_parentLabel . " or " . $this->_childLabel;
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
    $this->_contactSegmentId = $this->_submitValues['contact_segment_id'];
    if ($this->_submitValues['contact_id']) {
      $this->_contactId = $this->_submitValues['contact_id'];
    }
    $this->_contactSegmentId = $this->_submitValues['contact_segment_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveContactSegment($this->_submitValues);
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
    } else {
      $defaults['contact_segment_role'] = $this->_contactSegment['role_value'];
      if ($this->_parentSegmentId) {
        $defaults['segment_parent'] = $this->_parentSegmentId;
        $defaults['segment_child'] = $this->_contactSegment['segment_id'];
      } else {
        $defaults['segment_parent'] = $this->_contactSegment['segment_id'];
      }
      if ($this->_contactSegment['start_date']) {
        list($defaults['start_date']) = CRM_Utils_Date::setDateDefaults($this->_contactSegment['start_date']);
      }
      if ($this->_contactSegment['end_date']) {
        list($defaults['end_date']) = CRM_Utils_Date::setDateDefaults($this->_contactSegment['end_date']);
      }
    }
    return $defaults;
  }

  /**
   * Method to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $roleList = CRM_Contactsegment_Utils::getRoleList();
    $parentList = CRM_Contactsegment_Utils::getParentList();
    $childList = array("- select -") + CRM_Contactsegment_Utils::getChildList($this->_parentSegmentId);
    $this->add('hidden', 'contact_id');
    $this->add('hidden', 'contact_segment_id');
    $this->add('select', 'contact_segment_role', ts('Role'), $roleList, true);
    $this->add('select', 'segment_parent', ts($this->_parentLabel), $parentList, true);
    $this->add('select', 'segment_child', ts($this->_childLabel), $childList);
    $this->addDate('start_date', ts('Start Date'), true);
    $this->addDate('end_date', ts('End Date'), false);
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Method to save the contact segment
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
    $contactSegment = civicrm_api3('ContactSegment', 'Create', $params);
    $this->_contactSegment = $contactSegment['values'];
    $session = CRM_Core_Session::singleton();
    $session->setStatus("Contact linked to ".$segmentLabel." as ".$formValues['contact_segment_role'],
      "Contact Linked to ".$segmentLabel, "success");
  }

  /**
   * Method to end contact segment and return
   *
   */
  protected function closeContactSegmentAndReturn()
  {
    $this->_contactSegment['is_active'] = 0;
    $this->_contactSegment['end_date'] = date('Ymd');
    civicrm_api3('ContactSegment', 'Create', $this->_contactSegment);
    $session = CRM_Core_Session::singleton();
    $displayName = civicrm_api3('Contact', 'Getvalue',
      array('id' => $this->_contactSegment['contact_id'], 'return' => 'display_name'));
    $segment = civicrm_api3('Segment', 'Getsingle', array('id' => $this->_contactSegment['segment_id']));
    if (!$segment['parent_id']) {
      $statusMessage = $this->_parentLabel . " " . $segment['label'] . " with role " . $this->_contactSegment['role_value']
        . " ended for contact " . $displayName;
      $statusTitle = $this->_parentLabel . " ended";
    } else {
      $statusMessage = $this->_childLabel . " " . $segment['label'] . " with role " . $this->_contactSegment['role_value']
        . " ended for contact " . $displayName;
      $statusTitle = $this->_childLabel . " ended";
    }
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Method to validate if role is allowed for segment
   *
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validateRole($fields) {
    $errors = array();
    $segmentSettings = civicrm_api3('SegmentSetting', 'Getsingle', array());
    if ($fields['contact_segment_role']) {
      if ($fields['segment_child']) {
        if (!in_array($fields['contact_segment_role'], $segmentSettings['child_roles'])) {
          $errors['contact_segment_role'] = ts('Role not allowed for '.$segmentSettings['child_label']);
          return $errors;
        }
      }
      if ($fields['segment_parent']) {
        if (!in_array($fields['contact_segment_role'], $segmentSettings['parent_roles'])) {
          $errors['contact_segment_role'] = ts('Role not allowed for '.$segmentSettings['parent_label']);
          return $errors;
        }
      }
    }
    return TRUE;
  }
}
