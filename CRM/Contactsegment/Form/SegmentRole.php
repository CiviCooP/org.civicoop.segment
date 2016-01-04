<?php
require_once 'CRM/Core/Form.php';
/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 24 Januaryr 2016
 * @license AGPL-3.0
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_SegmentRole extends CRM_Core_Form {
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
    CRM_Utils_System::setTitle("Segment Settings - Unique Roles");
  }
  /**
   * Overridden parent method to process form
   *
   * @access public
   */
  function postProcess() {
    $this->saveSegmentRoles($this->exportValues());
    $session = CRM_Core_Session::singleton();
    $config = CRM_Core_Config::singleton();
    $session->setStatus("Segment Settings Saved", "Saved", "success");
    if ($session->readUserContext() == $config->userFrameworkBaseURL) {
      $session->pushUserContext(CRM_Utils_System::url('civicrm', 'reset=1', true));
    }
    parent::postProcess();
  }

  /**
   * Method to save segment setting roles
   *
   * @param array $formValues
   */
  private function saveSegmentRoles($formValues) {
    $params = array();
    $params['parent_label'] = $this->_segmentConfig['parent_label'];
    $params['child_label'] = $this->_segmentConfig['child_label'];
    foreach ($this->_segmentConfig['parent_roles'] as $parentRoleName => $parentRole) {
      $params['parent_roles'][$parentRoleName]['label'] = $parentRole['label'];
      if (in_array($parentRole['label'], $formValues['parent_uniques'])) {
        $params['parent_roles'][$parentRoleName]['unique'] = 1;
      } else {
        $params['parent_roles'][$parentRoleName]['unique'] = 0;
      }
    }
    foreach ($this->_segmentConfig['child_roles'] as $childRoleName => $childRole) {
      $params['child_roles'][$childRoleName]['label'] = $childRole['label'];
      if (in_array($childRole['label'], $formValues['child_uniques'])) {
        $params['child_roles'][$childRoleName]['unique'] = 1;
      } else {
        $params['child_roles'][$childRoleName]['unique'] = 0;
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
    $parentUniques = array();
    $childUniques = array();
    foreach ($this->_segmentConfig['parent_roles'] as $roleName => $role) {
      if ($role['unique'] == 1) {
        $parentUniques[] = $role['label'];
      }
    }
    foreach ($this->_segmentConfig['child_roles'] as $roleName => $role) {
      if ($role['unique'] == 1) {
        $childUniques[] = $role['label'];
      }
    }

    $defaults['parent_uniques'] = $parentUniques;
    $defaults['child_uniques'] = $childUniques;
    return $defaults;
  }

  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $parentRoles = array();
    foreach ($this->_segmentConfig['parent_roles'] as $parentRoleName => $parentRole) {
      $parentRoles[$parentRole['label']] = $parentRole['label'];
    }
    $childRoles = array();
    foreach ($this->_segmentConfig['child_roles'] as $childRoleName => $childRole) {
      $childRoles[$childRole['label']] = $childRole['label'];
    }
    $parentUniqueSelect = $this->addElement('advmultiselect', 'parent_uniques', ts('Roles'), $parentRoles,
      array('size' => 5, 'style' => 'width:280px', 'class' => 'advmultselect'),TRUE);
    $parentUniqueSelect->setButtonAttributes('add', array('value' => ts('Unique')." >>"));
    $parentUniqueSelect->setButtonAttributes('remove', array('value' => "<< ".ts('Not unique')));
    $childUniqueSelect = $this->addElement('advmultiselect', 'child_uniques', ts('Roles'), $childRoles,
      array('size' => 5, 'style' => 'width:280px', 'class' => 'advmultselect'),TRUE);
    $childUniqueSelect->setButtonAttributes('add', array('value' => ts('Unique')." >>"));
    $childUniqueSelect->setButtonAttributes('remove', array('value' => "<< ".ts('Not unique')));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
}
