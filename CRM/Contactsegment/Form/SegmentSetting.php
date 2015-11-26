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
    $config = CRM_Contactsegment_Config::singleton();
    $this->_segmentConfig = $config->getSegmentConfig();
    CRM_Utils_System::setTitle("Segment Settings");
  }

  /**
   * Overridden parent method to process form (calls parent method too)
   *
   * @access public
   */
  function postProcess() {
    $formValues = $this->exportValues();
    $this->_segmentConfig['parent']['label'] = $formValues['parent_label'];
    $this->_segmentConfig['parent']['roles'] = $formValues['parent_roles'];
    $this->_segmentConfig['child']['label'] = $formValues['child_label'];
    $this->_segmentConfig['child']['roles'] = $formValues['child_roles'];
    $config = CRM_Contactsegment_Config::singleton();
    $config->saveSegmentConfig($this->_segmentConfig);
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
    $defaults['parent_label'] = $this->_segmentConfig['parent']['label'];
    $defaults['parent_roles'] = $this->_segmentConfig['parent']['roles'];
    $defaults['child_label'] = $this->_segmentConfig['child']['label'];
    $defaults['child_roles'] = $this->_segmentConfig['child']['roles'];
    return $defaults;
  }

  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $roleList = $this->getRoleList();
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

  /**
   * Method to get select list of possible roles
   *
   * @access protected
   * @return array
   */
  protected function getRoleList() {
    $roleList = array();
    $config = CRM_Contactsegment_Config::singleton();
    $optionValueParams = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'is_active' => 1
    );
    $roles = civicrm_api3('OptionValue', 'Get', $optionValueParams);
    foreach ($roles['values'] as $optionValueId => $optionValue) {
      $roleList[$optionValue['value']] = $optionValue['label'];
    }
    return $roleList;
  }
}

