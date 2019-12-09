<?php 
  class local_social_course_participant extends local_social_course_model{
    protected $course;
    protected $user;
    protected $context;
    protected $enrolled;
    protected $roles;

    public function __construct($userid, $courseid){
      $this->course = self::get_course_from_id($courseid);
      $this->user = self::get_user_from_id($courseid);
      $this->context = context_course::instance($this->course->id);
      $this->enrolled = self::enrolled();
    }
    
    public function all_groups(){
      $groups = groups_get_all_groups($this->course->id);
      $groups = array_values($groups);
      return $groups;
    }

    // public function get_enrolled(){
    //   return array_values($this->enrolled);
    // }

    private function enrolled(){
      $users = get_enrolled_users($this->context, '', 0, self::user_fields("u."));
      foreach($users as $userid => $user){
        $user->pictureurl = self::picture_profile_url($user->id);
        $user->roles = self::roles_assigned($user->id);
        $user->groups = self::groups_assigned($user->id);
      }
      dd($users);
      return $users;
    }

    private function groups_assigned($userid){
      $groups =  self::all_groups_with_members();
      $assigned = array();
      foreach($groups as $group){
        foreach($group->members as $member){
          if($member->id == $userid){
            // $group = clone $group;
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
        // $group = clone $group;
        $group->members = groups_get_members($group->id, self::user_fields("u."));
        array_push($groups, $group);
      }
      return $groups;
    }

    private function picture_profile_url($userid){
      $url = new moodle_url("/user/pix.php/$userid/f1.jpg");
      return $url->out(false);
    }

    private function roles_assigned($userid){
      $assigned = [];
      $roles = new stdClass();
      $roles->existing = self::get_existing_roles(); 
      $roles->available = [];
      foreach($roles->existing as $role){
        $users = get_role_users($role->id, $this->context);
        $users = array_values($users);
        // if(count($users) > 0){
        //   self::add_role($role);
        //   foreach($users as $user){
        //     array_push($this->enrolled[$user->id]->roles, $role);
        //     if($user->id == $this->user->id){
        //       array_push($this->user->roles, $role);
        //     }
        //   }
        // }
      }
    }
    
    private function get_existing_roles(){
      global $DB;
      $sql = "select * from {role}";
      $rows = $DB->get_records_sql($sql, array());
      $rows = array_values($rows);
      return $rows;
    }

    // private function add_role($role){
    //   $is_setted = self::role_is_set($role);
    //   if(!$is_setted){
    //     array_push($this->roles, $role);
    //   }
    // }

    // private function role_is_set($role){
    //   $is_setted = false;
    //   foreach($this->roles as $setted_role){
    //     if($role->id == $setted_role->id){
    //       $is_setted = true;
    //       break;
    //     }
    //   }
    //   return $is_setted;
    // }
  }