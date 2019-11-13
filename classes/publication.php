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
        $share->name = $record->publication_name_shared;
        $share->groupid = $record->publication_groupid_shared;
        $share->roleid = $record->publication_roleid_shared;
        $share->type = $record->publication_type_shared; 
        $publication = new local_social_course_publication();
        $publication->id = $record->publication_id; 
        $publication->courseid = $record->publication_courseid;
        $publication->authorid = $record->author_id;
        $publication->comment = $record->publication_comment;
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
      $sql = "select p.id as publication_id, p.sc_courseid as publication_courseid,
              p.sc_comment as publication_comment, p.sc_groupid_shared as publication_groupid_shared, 
              p.sc_roleid_shared as publication_roleid_shared, p.sc_type_shared as publication_type_shared,
              p.sc_name_shared as publication_name_shared, p.sc_timecreated as publication_timecreated,
              p.sc_authorid as author_id, u.username as author_username, u.firstname as author_firstname, 
              u.lastname as author_lastname, u.email as author_email, (select lsl.timecreated from 
              {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as author_last_action, u.lastaccess as author_lastaccess from {sc_publications} p, {user} u where 
              p.sc_authorid = u.id and p.sc_authorid = ? and p.sc_courseid = ? and p.sc_timedeleted IS NULL 
              $query_fragment order by p.sc_timecreated desc";
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

    public static function shared_with($userid, $courseid, $startid = null){
      global $DB;
      $take = get_config('local_social_course', 'maxrecordsperquery');
      $params = array($userid, $courseid, $userid);
      $start_in_id = "";
      if(!empty($startid)){
        $start_in_id = ' and id <= ? ';
        array_push($params, $startid); 
      }
      $sql = "select p.id as publication_id, p.sc_courseid as publication_courseid, 
              p.sc_comment as publication_comment, p.sc_groupid_shared as publication_groupid_shared,
              p.sc_roleid_shared as publication_roleid_shared, p.sc_type_shared as publication_type_shared,
              p.sc_name_shared as publication_name_shared, p.sc_timecreated as publication_timecreated,
              p.sc_authorid as author_id, u.username as author_username, u.firstname as author_firstname, 
              u.lastname as author_lastname, u.email as author_email, (select lsl.timecreated from 
              {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as author_last_action, u.lastaccess as author_lastaccess from {sc_publications} p, {user} u, 
              {sc_recipients} r where p.sc_authorid = u.id and p.id = r.sc_publicationid and p.sc_authorid = ? 
              and p.sc_courseid = ? and r.sc_to = ? and r.sc_timedeleted IS NULL and p.sc_timedeleted IS NULL
              $start_in_id order by p.sc_timecreated desc";
      $publications = $DB->get_records_sql($sql, $params, null, $take);
      return $publications;
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
      $publication->sc_timecreated = time();
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