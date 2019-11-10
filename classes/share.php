<?php 
class local_social_course_share{

  public $name;
  public $groupid;
  public $roleid;
  public $type;

  public function get(){
    $shared = new stdClass();
    $shared->name = $this->name;
    $shared->type = $this->type;
    $shared->groupid = $this->groupid;
    $shared->roleid = $this->roleid;
    return $shared;
  }
}