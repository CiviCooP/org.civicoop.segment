<?php
/**
 * Class BAO SegmentSetting (no DAO attached, data in json file)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */
class CRM_Contactsegment_BAO_SegmentSetting {

  protected $_segmentSettingArray = array();

  /**
   * CRM_Contactsegment_BAO_SegmentSetting constructor.
   */
  function __construct() {
    $config = CRM_Contactsegment_Config::singleton();
    try {
      $this->_segmentSettingArray = $config->getJsonResourcesArray("segment_config.json");
    } catch (Exception $ex) {
      CRM_Core_Error::fatal("Could not load the contact segment configuration required for extension
        org.civicoop.contactsegment. Contact your system administrator! Error message: ".$ex->getMessage());
    }
  }

  /**
   * Method to get the segment settings
   * @param array $params ['level'] default value = 'all'
   * @return array
   */
  public function get($params) {
    if (isset($params['level']) || !empty($params['level'])) {
      $level = $params['level'];
    } else {
      $level = 'all';
    }
    switch ($level) {
      case 'parent':
        $result = array(
          'parent_label' => $this->_segmentSettingArray['parent']['label'],
          'parent_roles' => $this->_segmentSettingArray['parent']['roles'],
        );
        break;
      case 'child':
        $result = array(
          'child_label' => $this->_segmentSettingArray['child']['label'],
          'child_roles' => $this->_segmentSettingArray['child']['roles']
        );
        break;
      default:
        $result = array(
          'parent_label' => $this->_segmentSettingArray['parent']['label'],
          'parent_roles' => $this->_segmentSettingArray['parent']['roles'],
          'child_label' => $this->_segmentSettingArray['child']['label'],
          'child_roles' => $this->_segmentSettingArray['child']['roles']
        );
        break;
    }
    return array($result);
  }

  /**
   * Method to write the segment settings (for new ones and for updates)
   * @param $params
   * @return array $this->_segmentSetting
   */
  public function add($params) {
    if (!empty($params)) {
      if ($params['parent_label']) {
        $this->_segmentSettingArray['parent']['label'] = $params['parent_label'];
      }
      if ($params['parent_roles']) {
        $this->_segmentSettingArray['parent']['roles'] = $params['parent_roles'];
      }
      if ($params['child_label']) {
        $this->_segmentSettingArray['child']['label'] = $params['child_label'];
      }
      if ($params['child_roles']) {
        $this->_segmentSettingArray['child']['roles'] = $params['child_roles'];
      }
      $this->saveSegmentConfig();
      return $this->_segmentSettingArray;
    }
  }

  /**
   * Method to save the settings in json file in resources
   *
   * @throws Exception when not able to write settings
   */
  private function saveSegmentConfig() {
    $config = CRM_Contactsegment_Config::singleton();
    $fileName = $config->getResourcesPath() . 'segment_config.json';
    try {
      $fh = fopen($fileName, 'w');
      fwrite($fh, json_encode($this->_segmentSettingArray, JSON_PRETTY_PRINT));
      fclose($fh);
    } catch (Exception $ex) {
      throw new Exception('Could not open segment_config.json, contact your system administrator. Error reported: ' . $ex->getMessage());
    }
  }
}

