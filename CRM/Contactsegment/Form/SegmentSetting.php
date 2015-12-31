<?php
require_once 'CRM/Core/Form.php';
/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 24 November 2015
 * @license AGPL-3.0
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_SegmentSetting extends CRM_Core_Form {
  private $_segmentConfig = NULL;
  /**
   * Overridden parent method to buildQuickForm (call parent method too)
   *
   * @access public
   */
  function buildQuickForm() {
    $this->addFormElements();
    parent::buildQuickForm();
  }
  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_segmentConfig = civicrm_api3('SegmentSetting', 'Getsingle', array());
    CRM_Utils_System::setTitle("Segment Settings");
  }
  /**
   * Overridden parent method to process form (calls parent method too)
   *
   * @access public
   */
  function postProcess() {
    $this->saveSegmentSetting($this->exportValues());
    $session = CRM_Core_Session::singleton();
    $config = CRM_Core_Config::singleton();
    $session->setStatus("Segment Settings Saved", "Saved", "success");
    if ($session->readUserContext() == $config->userFrameworkBaseURL) {
      $session->pushUserContext(CRM_Utils_System::url('civicrm', 'reset=1', true));
    }
    parent::postProcess();
  }

  /**
   * Method to save segment settings
   *
   * @param array $formValues
   */
  private function saveSegmentSetting($formValues) {
    $params = array();
    $params['parent_label'] = $formValues['parent_label'];
    $params['child_label'] = $formValues['child_label'];
    foreach ($formValues['parent_roles'] as $parentRole) {
      $roleName = CRM_Contactsegment_Utils::getRoleNameWithLabel($parentRole);
      if (isset($this->_segmentConfig['parent_roles'][$roleName])) {
        $params['parent_roles'][$roleName] = $this->_segmentConfig['parent_roles'][$roleName];
      } else {
        $params['parent_roles'][$roleName] = array('label' => $parentRole, 'unique' => 0);
      }
    }
    foreach ($formValues['child_roles'] as $childRole) {
      $roleName = CRM_Contactsegment_Utils::getRoleNameWithLabel($childRole);
      if (isset($this->_segmentConfig['child_roles'][$roleName])) {
        $params['child_roles'][$roleName] = $this->_segmentConfig['child_roles'][$roleName];
      }
    }
    $this->_segmentConfig = civicrm_api3('SegmentSetting', 'create', $params);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $parentRoles = array();
    $childRoles = array();
    foreach ($this->_segmentConfig['parent_roles'] as $roleName => $role) {
      $parentRoles[] = $role['label'];
    }
    foreach ($this->_segmentConfig['child_roles'] as $roleName => $role) {
      $childRoles[] = $role['label'];
    }
    $defaults['parent_label'] = $this->_segmentConfig['parent_label'];
    $defaults['parent_roles'] = $parentRoles;
    $defaults['child_label'] = $this->_segmentConfig['child_label'];
    $defaults['child_roles'] = $childRoles;
    return $defaults;
  }
  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $roleList = CRM_Contactsegment_Utils::getRoleList();
    $this->add('text', 'parent_label', ts('Label'), array('size' => 128), true);
    $this->add('text', 'child_label', ts('Label'), array('size' => 128), true);
    $parentRoleSelect = $this->addElement('advmultiselect', 'parent_roles', ts('Roles'), $roleList,
      array('size' => 5, 'style' => 'width:280px', 'class' => 'advmultselect'),TRUE);
    $parentRoleSelect->setButtonAttributes('add', array('value' => ts('Use for parent')." >>"));
    $parentRoleSelect->setButtonAttributes('remove', array('value' => "<< ".ts('Not used for parent')));
    $childRoleSelect = $this->addElement('advmultiselect', 'child_roles', ts('Roles'), $roleList,
      array('size' => 5, 'style' => 'width:280px', 'class' => 'advmultselect'),TRUE);
    $childRoleSelect->setButtonAttributes('add', array('value' => ts('Use for child')." >>"));
    $childRoleSelect->setButtonAttributes('remove', array('value' => "<< ".ts('Not used for child')));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
}
