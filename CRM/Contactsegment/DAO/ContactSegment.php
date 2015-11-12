<?php

/**
 * Class DAO civicrm_contact_segment
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */

class CRM_Contactsegment_DAO_ContactSegment extends CRM_Core_DAO {
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  static $_export = null;
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_contact_segment';
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ) ,
        'contact_id' => array(
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'segment_id' => array(
          'name' => 'segment_id',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'role_value' => array(
          'name' => 'role_value',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 512
        ),
        'start_date' => array(
          'name' => 'start_date',
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'end_date' => array(
          'name' => 'end_date',
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'is_active' => array(
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_INT,
        ),
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the array key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'contact_id' => 'contact_id',
        'segment_id' => 'segment_id',
        'role_value' => 'role_value',
        'start_date' => 'start_date',
        'end_date' => 'end_date',
        'is_active' => 'is_active'
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['activity'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}