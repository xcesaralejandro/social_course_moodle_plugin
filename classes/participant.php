<?php 
  class local_social_course_participant extends local_social_course_model{
    protected $course;
    protected $user;
    protected $context;

    public function __construct($userid, $courseid){
      $this->classname = "local_social_course_participant";
      $this->obtainable = ["enrolled","roles"];
      $this->course = self::get_course_from_id($courseid);
      $this->user = self::get_user_from_id($userid);
      $this->context = context_course::instance($this->course->id);
    }

    public function all_groups(){
      $groups = groups_get_all_groups($this->course->id);
      $groups = self::filter_groups_by_group_mode($groups);
      $groups = self::arrayCopy($groups);
      return $groups;
    }

    private function filter_groups_by_group_mode($all_groups){
      $groups = new stdClass();
      $groups->all = $all_groups;
      $groups->is_member = array();
      if(self::current_user_is_admin()){
        return $groups->all;
      }
      if(self::separated_group()){
        $groups->is_member = self::current_user_groups();
      }
      return $groups->is_member;
    }

    private function current_user_is_admin(){
      $admins = get_admins();
      $is_admin = isset($admins[$this->user->id]);
      return $is_admin;
    }
    
    private function separated_group(){
      defined("SEPARATE_GROUPS") ?: define("SEPARATE_GROUPS", 1);
      $enabled = $this->course->groupmode == SEPARATE_GROUPS;
      return $enabled;
    }

    private function current_user_groups(){
      $groups = new stdClass();
      $groups->all = groups_get_all_groups($this->course->id); 
      $groups->current_user_is_member = array();
      foreach($groups->all as $group){
        $prefix = "u.";
        $members = groups_get_members($group->id, self::sql_query_user_fields($prefix));
        if(isset($members[$this->user->id])){
          $groups->current_user_is_member[$group->id] = $group;
        }
      }
      return $groups->current_user_is_member;
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
      $users = self::filter_user_list_by_group_mode($users);
      $users = self::add_profile_pictures($users);
      $users = self::add_groups($users);
      $users = self::add_roles($users);
      return $users;
    }

    private function filter_user_list_by_group_mode($all_users){
      $users = new stdClass();
      $users->all = $all_users;
      $users->filtered = array();
      if(self::current_user_is_admin()){
        return $users->all;
      }
      if(self::separated_group()){
        $groups = self::all_groups_with_members();
        foreach($groups as $group){
          foreach($group->members as $member){
            if(!isset($users->filtered[$member->id])){
              $users->filtered[$member->id] = $member;
            }
          }
        }
      }
      return $users->filtered;
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

    public function extract_different_roles_from($users){
      $roles = array();
      foreach($users as $user){
        foreach($user->roles as $role){
          if(!isset($roles[$role->id])){
            $roles[$role->id] = $role;
          }
        }
      }
      return $roles;
    }

  }