<?php 
  class local_social_course_core{

    protected function get_course_from_id($courseid, $strictness = MUST_EXIST){
      global $DB;
      $course = $DB->get_record("course", array("id" => $courseid), '*', $strictness);
      return $course;
    }

    protected function get_user_from_id($userid, $strictness = MUST_EXIST){
      global $DB;
      $user = $DB->get_record("user", array("id" => $userid), '*', $strictness);
      return $user;
    }

    protected function user_fields($prefix = null, $renameid = null){
      $query = "";
      $fields = ["id", "username", "firstname", "lastname", "email", "lastaccess"];
      $last = count($fields) - 1;
      foreach($fields as $iteration => $field){
        if($iteration == 0 && $renameid){
          $query .= "$prefix$renameid";
        }else{
          $query .= "$prefix$field";
        }
        if($iteration < $last){
          $query .= ", ";
        }
      }
      return $query;
    }

    // const  USER_FIELDS = "u.id, u.username, u.firstname, u.lastname, u.email, u.lastaccess";
    // protected $course;
    // protected $context;
    // protected $user;
    // protected $groups;
    // protected $enrolled;
    // protected $roles;

    // public function __construct($courseid, $userid){
    //   $this->course = self::get_course_from_id($courseid);
    //   $this->user = self::get_user_from_id($userid);
    //   $this->context = context_course::instance($this->course->id);
    //   $this->groups = groups_get_all_groups($this->course->id);
    //   self::set_enrolled_users();
    //   self::set_roles();
    // }

    // public function get_course_from_id($courseid){
    //   global $DB;
    //   $course = $DB->get_record("course", array("id" => $courseid), '*', MUST_EXIST);
    //   return $course;
    // }

    // public function get_user_from_id($userid){
    //   global $DB;
    //   $user = $DB->get_record("user", array("id" => $userid), '*', MUST_EXIST);
    //   $user->roles = [];
    //   return $user;
    // }

    // public function get_availables_roles(){
    //   return $this->roles;
    // }

    // public function get_groups(){
    //   return array_values($this->groups);
    // }

    // public function get_enrolled(){
    //   return array_values($this->enrolled);
    // }

    // private function set_enrolled_users(){
    //   $groups = self::get_groups_with_members();
    //   $users = get_enrolled_users($this->context, '', 0, self::USER_FIELDS);
    //   foreach($users as $userid => $user){
    //     $user->pictureurl = self::get_picture_url($user->id);
    //     $user->roles = [];
    //     $user->groups = self::find_user_groups($groups, $user->id);
    //   }
    //   $this->enrolled = $users;
    // }
    
    // public function get_groups_with_members(){
    //   $groups = array();
    //   foreach($this->groups as $group){
    //     $group = clone $group;
    //     $group->members = groups_get_members($group->id, self::USER_FIELDS);
    //     array_push($groups, $group);
    //   }
    //   return $groups;
    // }

    // private function find_user_groups($groups, $memberid){
    //   $user_groups = array();
    //   foreach($groups as $groupid => $group){
    //     foreach($group->members as $member){
    //       if($member->id == $memberid){
    //         $group_copy = clone $group;
    //         unset($group_copy->members);
    //         array_push($user_groups, $group_copy);
    //         continue;
    //       }
    //     }
    //   }
    //   return $user_groups;
    // }

    // private function get_picture_url($userid){
    //   $url = new moodle_url("/user/pix.php/$userid/f1.jpg");
    //   return $url->out(false);
    // }

    // public function set_roles(){
    //   $this->roles = [];
    //   $roles = new stdClass();
    //   $roles->existing = self::get_existing_roles(); 
    //   $roles->available = [];
    //   foreach($roles->existing as $role){
    //     $users = get_role_users($role->id, $this->context);
    //     $users = array_values($users);
    //     if(count($users) > 0){
    //       self::add_role($role);
    //       foreach($users as $user){
    //         array_push($this->enrolled[$user->id]->roles, $role);
    //         if($user->id == $this->user->id){
    //           array_push($this->user->roles, $role);
    //         }
    //       }
    //     }
    //   }
    // }

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

    // private function get_existing_roles(){
    //   global $DB;
    //   $sql = "select * from {role}";
    //   $rows = $DB->get_records_sql($sql, array());
    //   $rows = array_values($rows);
    //   return $rows;
    // }
  }