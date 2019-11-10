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
      $publications = self::get_publications($userid, $courseid);
      return $publications;
    }

    public static function get_publications ($userid, $courseid, $take = 10, $startid = null){
      global $DB;
      $params = array($userid, $courseid);
      $query_fragment = "";
      if(!empty($startid)){
        $query_fragment = ' and id <= ?';
        array_push($params, $startid); 
      }
      $sql = "select * from {sc_publications} where sc_authorid = ? and sc_courseid = ? $query_fragment
              order by id desc";
      $results = $DB->get_records_sql($sql, $params, null, $take);
      dd($results);
      return $results;
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
      self::make_author_recipient();
      $publication = self::get();
      return $publication;
    }

    private function make_author_recipient(){
      $recipient = new local_social_course_recipient();
      $recipient->recipientid = $this->authorid;
      $recipient->publicationid = $this->id;
      $recipient->save();
      $recipient = $recipient->get();
      array_push($this->recipients, $recipient);
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