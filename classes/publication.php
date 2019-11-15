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

    public static function created_by ($userid, $courseid, $startid = null){
      global $DB;
      $take = get_config('local_social_course', 'maxrecordsperquery');
      $params = array($userid, $courseid);
      $query_fragment = "";
      if(!empty($startid)){
        $query_fragment = ' and id <= ? ';
        array_push($params, $startid); 
      }
      $sql = "select p.id as publication_id, p.sc_courseid as publication_courseid,
              p.sc_comment as publication_comment, p.sc_groupid_shared as share_groupid, 
              p.sc_roleid_shared as share_roleid, p.sc_type_shared as share_type,
              p.sc_name_shared as share_name, p.sc_timecreated as publication_timecreated,
              p.sc_authorid as author_id, u.username as author_username, u.firstname as author_firstname, 
              u.lastname as author_lastname, u.email as author_email, (select lsl.timecreated from 
              {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as author_last_action, u.lastaccess as author_lastaccess from {sc_publications} p, {user} u where 
              p.sc_authorid = u.id and p.sc_authorid = ? and p.sc_courseid = ? and p.sc_timedeleted IS NULL 
              $query_fragment order by p.sc_timecreated desc";
      $rows = $DB->get_records_sql($sql, $params, null, $take);
      $publications = self::transform_to_classes($rows);
      return $publications;
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
              p.sc_comment as publication_comment, p.sc_groupid_shared as share_groupid,
              p.sc_roleid_shared as share_roleid, p.sc_type_shared as share_type,
              p.sc_name_shared as share_name, p.sc_timecreated as publication_timecreated,
              p.sc_authorid as author_id, u.username as author_username, u.firstname as author_firstname, 
              u.lastname as author_lastname, u.email as author_email, (select lsl.timecreated from 
              {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as author_last_action, u.lastaccess as author_lastaccess from {sc_publications} p, {user} u, 
              {sc_recipients} r where p.sc_authorid = u.id and p.id = r.sc_publicationid and p.sc_authorid = ? 
              and p.sc_courseid = ? and r.sc_to = ? and r.sc_timedeleted IS NULL and p.sc_timedeleted IS NULL
              $start_in_id order by p.sc_timecreated desc";
      $rows = $DB->get_records_sql($sql, $params, null, $take);
      $publications = self::transform_to_classes($rows);
      return $publications;
    }

    public function lazy_loading($modules){
      if(in_array('comments', $modules)){
        self::set_comments();
      }
      if(in_array('recipients', $modules)){
        self::set_recipients();
      }
      if(in_array('attachments', $modules)){
        self::set_attachments();
      }
    }

    private function set_recipients(){
      $this->recipients = local_social_course_recipient::all($this->id);
    }

    private function set_attachments(){
      
    }

    private function set_comments(){
      $comments_number = get_config('local_social_course', 'visiblecomments');
      if($comments_number > 0){
        $this->comments = local_social_course_comment::all($this->id, $comments_number);
      }
    }

    public static function transform_to_classes($rows){
      $publications = array();
      foreach($rows as $key => $row){
        $share = new local_social_course_share();
        $share->name = $row->share_name;
        $share->groupid = $row->share_groupid;
        $share->roleid = $row->share_roleid;
        $share->type = $row->share_type;
        $publication = new local_social_course_publication();
        $publication->id = $row->publication_id;
        $publication->courseid = $row->publication_courseid;
        $publication->authorid = $row->author_id;
        $publication->comment = $row->publication_comment;
        $publication->share = $share;
        $publication->comments = [];
        $publication->attachments = [];
        $publication->recipients = [];
        $publication->lazy_loading(["comments", "attachments", "recipients"]);
        array_push($publications,$publication);
      }
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