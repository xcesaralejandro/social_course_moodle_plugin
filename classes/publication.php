<?php 
  class local_social_course_publication{
    const TEACHER_ROLES = array(3);
    const STUDENT_ROLES = array(5);
    const  USER_FIELDS = "u.id, u.username, u.firstname, u.lastname, u.email, u.lastaccess";
    public $course;
    public $context;
    public $user;
    public $groups;
    public $enrolled_users;

    public function __construct($courseid, $userid){
      global $DB;
      $this->course = $DB->get_record("course", array("id" => $courseid), '*', MUST_EXIST);
      $this->user = $DB->get_record("user", array("id" => $userid), '*', MUST_EXIST);
      $this->context = context_course::instance($this->course->id);
      $this->groups = groups_get_all_groups($this->course->id);
      self::set_enrolled_users();
    }

    private function set_enrolled_users(){
      $groups = self::get_groups_with_members();
      $users = get_enrolled_users($this->context, '', 0, self::USER_FIELDS);
      foreach($users as $userid => $user){
        $user->pictureurl = self::get_picture_url($user->id);
        $user->groups = self::find_user_groups($groups, $user->id);
      }
      $this->enrolled_users = $users;
    }
    
    public function get_groups_with_members(){
      $groups = array();
      foreach($this->groups as $group){
        $group = clone $group;
        $group->members = groups_get_members($group->id, self::USER_FIELDS);
        array_push($groups, $group);
      }
      return $groups;
    }

    private function find_user_groups($groups, $memberid){
      $user_groups = array();
      foreach($groups as $groupid => $group){
        foreach($group->members as $member){
          if($member->id == $memberid){
            $group_copy = clone $group;
            unset($group_copy->members);
            array_push($user_groups, $group_copy);
            continue;
          }
        }
      }
      return $user_groups;
    }

    private function get_picture_url($userid){
      $url = new moodle_url("/user/pix.php/$userid/f1.jpg");
      return $url->out(false);
    }
  }