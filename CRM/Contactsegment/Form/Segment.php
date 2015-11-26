<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 November 2015
 * @license AGPL-3.0
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_Segment extends CRM_Core_Form {

  protected $_segmentId = NULL;
  protected $_segment = array();
  protected $_parentLabel = NULL;
  protected $_childLabel = NULL;

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
    $this->_segmentId = CRM_Utils_Request::retrieve('sid', 'Integer');
    $this->getSegmentLabels();
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionLabel = "Add";
        $segmentTypeLabel = $this->_parentLabel." or ".$this->_childLabel;
        break;
      case CRM_Core_Action::UPDATE:
        $this->_segment = civicrm_api3('Segment', 'Getsingle', array('id' => $this->_segmentId));
        $actionLabel = "Edit";
        if (!$this->_segment['parent_id']) {
          $segmentTypeLabel = $this->_parentLabel;
        } else {
          $segmentTypeLabel = $this->_childLabel;
        }
        break;
      case CRM_Core_Action::VIEW:
        $this->_segment = civicrm_api3('Segment', 'Getsingle', array('id' => $this->_segmentId));
        $actionLabel = "View";
        if (!$this->_segment['parent_id']) {
          $segmentTypeLabel = $this->_parentLabel;
        } else {
          $segmentTypeLabel = $this->_childLabel;
        }
        break;
    }
    CRM_Utils_System::setTitle($segmentTypeLabel);
    $this->assign('actionLabel', $actionLabel);
    $this->assign('segmentTypeLabel', $segmentTypeLabel);
      if ($this->_action == CRM_Core_Action::DELETE) {
        $this->deleteSegmentAndReturn();
    }
  }

  /**
   * Overridden parent method to process form (calls parent method too)
   *
   * @access public
   */
  function postProcess() {
    $this->_segmentId = $this->_submitValues['segment_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveSegment($this->_submitValues);
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
    $defaults['segment_id'] = $this->_segmentId;
    if (!$this->_segment['parent_id']) {
      $defaults['segment_type'] = 'parent';
    } else {
      $defaults['segment_type'] = 'child';
    }
    if ($this->_action == CRM_Core_Action::VIEW || $this->_action == CRM_Core_Action::UPDATE) {
      $defaults['segment_label'] = $this->_segment['label'];
      if ($this->_segment['parent_id']) {
        $defaults['segment_parent'] = $this->_segment['parent_id'];
      }
    }
    return $defaults;
  }

  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $config = CRM_Contactsegment_Config::singleton();
    $parentList = $this->getParentList();
    $this->add('hidden', 'segment_id');
    $this->add('text', 'segment_type', ts('Type'));
    $this->add('text', 'segment_label', ts('Label'), array('size' => 128), true);
    if ($this->_action == CRM_Core_Action::ADD) {
      $types = array($this->_parentLabel, $this->_childLabel);
      foreach ($types as $key => $var) {
        $typeOptions[$key] = HTML_QuickForm::createElement('radio', $key, ts('Type'), $var, $key);
      }
      $this->addGroup($typeOptions, 'segment_type_list', ts('Type'));
    }
    $this->add('select', 'segment_parent', ts('Parent'), $parentList);
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Function to save the segment
   *
   * @param $formValues
   * @access protected
   */
  protected function saveSegment($formValues) {
    // TODO : implement function to save segment

  }

  /**
   * Method to delete segment
   *
   */
  protected function deleteSegmentAndReturn() {
    // TODO : refactor to api function
    $config = CRM_Contactsegment_Config::singleton();
    if (!$this->_segmentParentId) {
      $statusMessage = $config->getParentSegmentLabel().$this->_segment['label']." deleted";
      $statusTitle = $config->getParentSegmentLabel()." deleted";
    } else {
      $statusMessage = $config->getChildSegmentLabel().$this->_segment['label']." from "
        .$config->getParentSegmentLabel()." "
        .CRM_Contactsegment_BAO_Segment::getSegmentLabelWithId($this->_segment['parent_id'])." deleted";
      $statusTitle = $config->getChildSegmentLabel()." deleted";
    }
    CRM_Contactsegment_BAO_Segment::deleteById($this->_segmentId);
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Method to get select list of possible parent segments
   *
   * @access protected
   * @return array
   */
  protected function getParentList() {
    $parentList = array();
    $parentList[0] = "- select -";
    $parents = CRM_Contactsegment_BAO_Segment::getParentSegments();
    foreach ($parents as $parentId => $parent) {
      $parentList[$parentId] = $parent['label'];
    }
    asort($parentList);
    return $parentList;
  }
}

