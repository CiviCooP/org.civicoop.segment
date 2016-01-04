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
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->getContactSegment();
    } else {
      $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Integer');
    }
    $this->getSegmentLabels();
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionLabel = "Add";
        break;
      case CRM_Core_Action::CLOSE:
        $this->closeContactSegmentAndReturn();
        break;
      case CRM_Core_Action::UPDATE:
        $actionLabel = "Edit";
        break;
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
    if ($this->_parentSegmentId) {
      $childList = array("- select -") + CRM_Contactsegment_Utils::getChildList($this->_parentSegmentId);
    } else {
      if (isset($this->_contactSegment['segment_id'])) {
        $childList = array("- select -") + CRM_Contactsegment_Utils::getChildList($this->_contactSegment['segment_id']);
      } else {
        $defaultParentId = CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_segment where parent_id IS NULL ORDER BY label ASC LIMIT 1');
        $childList = array("- select -") + CRM_Contactsegment_Utils::getChildList($defaultParentId);
      }
    }
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
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Contactsegment_Form_ContactSegment', 'validateRoleAllowed'));
    $this->addFormRule(array('CRM_Contactsegment_Form_ContactSegment', 'validateRoleUnique'));
    $this->addFormRule(array('CRM_Contactsegment_Form_ContactSegment', 'validateExists'));
    $this->addFormRule(array('CRM_Contactsegment_Form_ContactSegment', 'validateEndDate'));
  }

  /**
   * Method to end contact segment and return
   *
   */
  protected function closeContactSegmentAndReturn() {
    $this->_contactSegment['is_active'] = 0;
    $startDate = new DateTime($this->_contactSegment['start_date']);
    $nowDate = new DateTime();
    if ($startDate > $nowDate) {
      $endDate = $startDate->modify('+1 day');
      $this->_contactSegment['end_date'] = $endDate->format('d-m-Y');
    } else {
      $this->_contactSegment['end_date'] = $nowDate->format('d-m-Y');
    }
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
   * Method to get the current contact segment data
   *
   * @access protected
   */
  protected function getContactSegment() {
    if (empty($this->_submitValues)) {
      $this->_contactSegmentId = CRM_Utils_Request::retrieve('csid', 'Integer');
      $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Integer');
    } else {
      $this->_contactSegmentId = $this->_submitValues['contact_segment_id'];
      $this->_contactId = $this->_submitValues['contact_id'];
    }
    $this->_contactSegment = civicrm_api3('ContactSegment', 'Getsingle', array('id' => $this->_contactSegmentId));
    $this->_parentSegmentId = civicrm_api3('Segment', 'Getvalue',
      array('id' => $this->_contactSegment['segment_id'], 'return' => 'parent_id'));
  }

  /**
   * Method to validate if role is allowed for segment
   *
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validateRoleAllowed($fields) {
    $errors = array();
    $segmentSettings = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $roleName = CRM_Contactsegment_Utils::getRoleNameWithLabel($fields['contact_segment_role']);
    if ($fields['contact_segment_role']) {
      if ($fields['segment_child']) {
        if (!isset($segmentSettings['child_roles'][$roleName])) {
          $errors['contact_segment_role'] = ts('Role not allowed for '.$segmentSettings['child_label']);
          $errors['segment_child'] = ts('Role not allowed for '.$segmentSettings['child_label']);
          return $errors;
        }
      }
      if ($fields['segment_parent']) {
        if (!isset($segmentSettings['parent_roles'][$roleName])) {
          $errors['contact_segment_role'] = ts('Role not allowed for '.$segmentSettings['parent_label']);
          $errors['segment_parent'] = ts('Role not allowed for '.$segmentSettings['parent_label']);
          return $errors;
        }
      }
    }
    return TRUE;
  }

  /**
   * Method to validate if contact segment already exists
   *
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validateExists($fields) {
    $errors = array();
    $segmentSettings = civicrm_api3('SegmentSetting', 'Getsingle', array());
    // only if empty contact_segment_id, meaning it is add action
    if (empty($fields['contact_segment_id'])) {
      if (!$fields['segment_child']) {
        $countSegment = civicrm_api3('ContactSegment', 'Getcount', array(
          'contact_id' => $fields['contact_id'],
          'role_value' => $fields['contact_segment_role'],
          'segment_id' => $fields['segment_parent']));
        if ($countSegment > 0) {
          $errors['segment_parent'] = ts('Contact is already linked to '.$segmentSettings['parent_label']
            .', edit the existing link if required');
          return $errors;
        }
      } else {
        $countSegment = civicrm_api3('ContactSegment', 'Getcount', array(
          'contact_id' => $fields['contact_id'],
          'role_value' => $fields['contact_segment_role'],
          'segment_id' => $fields['segment_child']));
        if ($countSegment > 0) {
          $errors['segment_child'] = ts('Contact is already linked to '.$segmentSettings['child_label']
            .', edit the existing link if required');
          return $errors;
        }
      }
    }
    return TRUE;
  }

  /**
   * Method to validate if end date is not earlier than or equal to start date
   *
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validateEndDate($fields) {
    $errors = array();
    if ($fields['end_date']) {
      if ($fields['start_date']) {
        $endDate = new DateTime($fields['end_date']);
        $startDate = new DateTime($fields['start_date']);
        if ($endDate <= $startDate) {
          $errors['end_date'] = ts('End Date has to be later than Start Date');
          return $errors;
        }
      } else {
        $errors['end_date'] = ts('End Date has to be later than Start Date');
        return $errors;
      }
    }
    return TRUE;
  }

  /**
   * Method to validate if role is unique and already active
   *
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validateRoleUnique($fields) {
    $errors = array();
    $checkParams = array(
      'role' => $fields['contact_segment_role'],
      'contact_id' => $fields['contact_id'],
      'start_date' => $fields['start_date'],
      'end_date' => $fields['end_date']);
    if (!empty($fields['segment_child'])) {
      if (CRM_Contactsegment_Utils::isSegmentRoleUnique($fields['contact_segment_role'], 'child') == TRUE) {
        $checkParams['segment_id'] = $fields['segment_child'];
        if (CRM_Contactsegment_Utils::activeCurrentContactSegmentForRole($checkParams) != FALSE) {
          $errors['segment_child'] = ts('Only 1 active role allowed, there is already an active '.$fields['contact_segment_role']);
          return $errors;
        }
      }
    } else {
      if (CRM_Contactsegment_Utils::isSegmentRoleUnique($fields['contact_segment_role'], 'parent') == TRUE) {
        $checkParams['segment_id'] = $fields['segment_parent'];
        if (CRM_Contactsegment_Utils::activeCurrentContactSegmentForRole($checkParams) != FALSE) {
          $errors['segment_parent'] = ts('Only 1 active role allowed, there is already an active '.$fields['contact_segment_role']);
          return $errors;
        }
      }
    }
    return TRUE;
  }
}
