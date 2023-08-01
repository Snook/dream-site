<?php
/**
 * Table Definition for task_retry_queue
 */
require_once 'DAO.inc';

class DAO_Task_retry_queue extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'task_retry_queue';    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $task_type;                       // char(26)  
    public $data;                            // blob(65535)  blob
    public $start_time;                      // datetime(19)  
    public $run_time;                        // int(11)  group_by
    public $pause_interval;                        // int(11)  group_by
    public $last_run_time;                   // datetime(19)  
    public $created_by;                      // mediumint(11)  unsigned group_by
    public $completion_code;                 // tinyint(2)  group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
