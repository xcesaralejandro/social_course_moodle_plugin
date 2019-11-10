<?php 
class local_social_course_share{

  private $name;
  private $groupid;
  private $roleid;
  private $type;

  public function __construct($name, $type, $roleid, $groupid){
    $this->name = $name;
    $this->type = $type;
    $this->roleid = $roleid;
    $this->groupid = $groupid;
  }

  public function get(){
    $shared = new stdClass();
    $shared->name = $this->name;
    $shared->type = $this->type;
    $shared->groupid = $this->groupid;
    $shared->roleid = $this->roleid;
    return $shared;
  }
}