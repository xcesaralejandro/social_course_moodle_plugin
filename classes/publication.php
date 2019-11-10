<?php 
  class local_social_course_publication{
    public $id;
    public $courseid;
    public $authorid;
    public $comment;
    public $share;
    public $comments; 
    public $attachments;
    public $recipients;

    public function __construct(){
      $this->attachments = array();     
      $this->recipients = array();     
      $this->comments = array();    
    }
    
    public static function find($publicationid){
      global $DB;

   }

    public static function created_by($userid, $courseid){
      $publications = [];
      $records = self::get_publications($userid, $courseid);
      foreach($records as $record){
        $share = new local_social_course_share();
        $share->name = $record->sc_name_shared;
        $share->groupid = $record->sc_groupid_shared;
        $share->roleid = $record->sc_roleid_shared;
        $share->type = $record->sc_type_shared; 
        $publication = new local_social_course_publication();
        $publication->id = $record->id; 
        $publication->courseid = $record->sc_courseid;
        $publication->authorid = $record->sc_authorid;
        $publication->comment = $record->sc_comment;
        $publication->share = $share->get();
        $publication->lazy_loading();
        array_push($publications, $publication->get());
      }
      return $publications;
    }

    public static function get_publications ($userid, $courseid, $startid = null){
      global $DB;
      $take = get_config('local_social_course', 'maxrecordsperquery');
      $params = array($userid, $courseid);
      $query_fragment = "";
      if(!empty($startid)){
        $query_fragment = ' and id <= ? ';
        array_push($params, $startid); 
      }
      $sql = "select * from {sc_publications} where sc_authorid = ? and sc_courseid = ? 
              and sc_timedeleted IS NULL $query_fragment order by id desc";
      $publications = $DB->get_records_sql($sql, $params, null, $take);
      return $publications;
    }

    public function lazy_loading(){
      self::set_comments();
    }

    private function set_recipients(){

    }

    private function set_attachments(){
      
    }

    private function set_comments(){
      $visible_comments = get_config('local_social_course', 'visiblecomments');
      if($visible_comments > 0){
        $this->comments = local_social_course_comment::get_all_from_publication($this->id, $visible_comments);
      }
    }

    public static function shared_with($userid){
      global $DB;

    }
    
    public function get(){
      $publication = new stdClass();
      $publication->id = $this->id;
      $publication->courseid = $this->courseid;
      $publication->authorid = $this->authorid;
      $publication->comment = $this->comment;
      $publication->share = $this->share;
      $publication->comments = $this->comments;
      $publication->attachments = $this->attachments;
      $publication->recipients = $this->recipients;
      return $publication;
    }

    public function save(){
      if(!self::validate_properties()){
        return null;
      }
      global $DB;
      $publication = new stdClass();
      $publication->sc_courseid = $this->courseid;
      $publication->sc_authorid = $this->authorid;
      $publication->sc_comment = $this->comment;
      $publication->sc_groupid_shared = $this->share->groupid;
      $publication->sc_roleid_shared = $this->share->roleid;
      $publication->sc_type_shared = $this->share->type;
      $publication->sc_name_shared = $this->share->name;
      $id = $DB->insert_record("sc_publications", $publication, true);
      $this->id = $id;
      $publication = self::get();
      return $publication;
    }

    private function validate_properties(){
      $valid = false;
      if(!empty($this->courseid) && !empty($this->authorid) && !empty($this->comment) && 
          !empty($this->share) && gettype($this->recipients) == "array"){
        $valid = true;
      }
      return $valid;
    }

    
  }