<?php 
  class local_social_course_publication{
    const  USER_FIELDS = "u.id, u.username, u.firstname, u.lastname, u.email, u.lastaccess, u.picture";
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
      self::set_users_inside_groups();
      self::set_enrolled_users();
    }

    private function set_users_inside_groups(){
      foreach($this->groups as $groupid => $group){
        $group->users = groups_get_members($groupid, self::USER_FIELDS);
        foreach($group->users as $user){
          $user->pictureurl = self::get_user_picture_url($user->id);
        }
      }
    }

    private function set_enrolled_users(){
      $users = get_enrolled_users($this->context, '', 0, self::USER_FIELDS);
      foreach($users as $userid => $user){
        $user->pictureurl = self::get_user_picture_url($user->id);
      }
      $this->enrolled_users = $users;
    }

    private function get_user_picture_url($userid){
      $url = new moodle_url("/user/pix.php/$userid/f1.jpg");
      return $url->out(false);
    }
  }