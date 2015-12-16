<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:ContactSegment.Disable',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Disable Expired ContactSegments',
      'description' => 'Disable all Contact Segments where End Date is less than today',
      'run_frequency' => 'Daily',
      'api_entity' => 'ContactSegment',
      'api_action' => 'Disable',
      'parameters' => '',
      'is_active' => 1,
      'is_reserved' => 1
    ),
  ),
);