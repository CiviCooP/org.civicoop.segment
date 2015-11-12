<?php

/**
 * Class DAO civicrm_segment
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 November 2015
 * @license AGPL-3.0
 */

class CRM_Contactsegment_DAO_Segment extends CRM_Core_DAO {
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
    return 'civicrm_segment';
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
        'name' => array(
          'name' => 'name',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 128,
        ) ,
        'label' => array(
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 256,
        ),
        'parent_id' => array(
          'name' => 'parent_id',
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
        'name' => 'name',
        'label' => 'label',
        'parent_id' => 'parent_id'
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