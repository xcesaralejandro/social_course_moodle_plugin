<?php 
  class local_social_course_participant extends local_social_course_model{
    protected $course;
    protected $user;
    protected $context;

    public function __construct($userid, $courseid){
      $this->classname = "local_social_course_participant";
      $this->obtainable = ["enrolled","roles"];
      $this->course = self::get_course_from_id($courseid);
      $this->user = self::get_user_from_id($courseid);
      $this->context = context_course::instance($this->course->id);
    }
    
    public function all_groups(){
      $groups = groups_get_all_groups($this->course->id);
      $groups = self::arrayCopy($groups);
      return $groups;
    }

    public function all_groups_with_members(){
      $groups = array();
      foreach(self::all_groups() as $group){
        $prefix = "u.";
        $group->members = groups_get_members($group->id, self::sql_query_user_fields($prefix));
        $groups[$group->id] = $group; 
      }
      return $groups;
    }

    public function enrolled(){
      $prefix = "u.";
      $users = get_enrolled_users($this->context, '', 0, self::sql_query_user_fields($prefix));
      $users = array_values($users);
      $users = self::add_profile_pictures($users);
      $users = self::add_groups($users);
      $users = self::add_roles($users);
      return $users;
    }
    
    private function add_profile_pictures($users){
      foreach($users as $user){
        $user->pictureurl = self::picture_profile_url($user->id);
      }
      return $users;
    }

    private function add_groups($users){
      $groups = new stdClass();
      $groups->all = self::all_groups();
      $groups->with_members = self::all_groups_with_members();
      foreach($users as $user){
        $user->groups = array();
        foreach($groups->with_members as $group){
          if(isset($group->members[$user->id])){
            array_push($user->groups, $groups->all[$group->id]);
          }
        }
      }
      return $users;
    }

    private function add_roles($users){
      $roles = self::all_platform_roles(); 
      foreach($users as $user){
        $user->roles = array();
        foreach($roles as $role){
          $role_users = get_role_users($role->id, $this->context);
          if(!empty($role_users) && isset($role_users[$user->id])){
            array_push($user->roles, $role);
          }
        }  
      }
      return $users;
    }
    
    

    // private function add_role($role){
    //   $is_setted = self::role_was_added($role);
    //   if(!$is_setted){
    //     array_push($this->roles, $role);
    //   }
    // }

    // private function role_was_added($role){
    //   $added = false;
    //   foreach($this->roles as $setted_role){
    //     if($role->id == $setted_role->id){
    //       $added = true;
    //       break;
    //     }
    //   }
    //   return $added;
    // }
  }