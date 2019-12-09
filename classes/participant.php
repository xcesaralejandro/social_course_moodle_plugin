<?php 
  class local_social_course_participant extends local_social_course_model{
    protected $course;
    protected $user;
    protected $context;
    protected $enrolled;
    protected $roles;

    public function __construct($userid, $courseid){
      $this->classname = "local_social_course_participant";
      $this->obtainable = ["enrolled","roles"];
      $this->course = self::get_course_from_id($courseid);
      $this->user = self::get_user_from_id($courseid);
      $this->context = context_course::instance($this->course->id);
      $this->enrolled = self::enrolled();
      $this->roles = self::roles_used();
      dd($this->enrolled);
    }
    
    public function all_groups(){
      $groups = groups_get_all_groups($this->course->id);
      $groups = array_values($groups);
      return $groups;
    }

    private function enrolled(){
      $prefix = "u.";
      $users = get_enrolled_users($this->context, '', 0, self::gettable_user_fields($prefix));
      foreach($users as $userid => $user){
        $user->pictureurl = self::picture_profile_url($user->id);
        $user->roles = [];
        $user->groups = self::groups_assigned($user->id);
      }
      return $users;
    }

    private function groups_assigned($userid){
      $groups =  self::all_groups_with_members();
      $assigned = array();
      foreach($groups as $group){
        foreach($group->members as $member){
          if($member->id == $userid){
            unset($group->members);
            array_push($assigned, $group);
            break;
          }
        }
      }
      return $assigned;
    }

    public function all_groups_with_members(){
      $groups = array();
      foreach(self::all_groups() as $group){
        $prefix = "u.";
        $group->members = groups_get_members($group->id, self::gettable_user_fields($prefix));
        array_push($groups, $group);
      }
      return $groups;
    }

    private function roles_used(){
      $this->roles = [];
      $roles = self::all_roles(); 
      foreach($roles as $role){
        $users = get_role_users($role->id, $this->context);
        $users = array_values($users);
        if(!empty($users)){
          $role->users = self::clean_users_properties($users);
          self::add_role($role);
        }
      }
      return $this->roles;
    }
    
    private function all_roles(){
      global $DB;
      $sql = "select * from {role}";
      $rows = $DB->get_records_sql($sql, array());
      $rows = array_values($rows);
      return $rows;
    }

    private function add_role($role){
      $is_setted = self::role_was_added($role);
      if(!$is_setted){
        array_push($this->roles, $role);
      }
    }

    private function role_was_added($role){
      $added = false;
      foreach($this->roles as $setted_role){
        if($role->id == $setted_role->id){
          $added = true;
          break;
        }
      }
      return $added;
    }
  }