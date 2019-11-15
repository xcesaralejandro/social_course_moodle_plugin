<?php 
  class local_social_course_comment{

    public $id;
    public $content;
    public $publicationid;
    public $author;
    public $timecreated;
    public $timedeleted;

    public static function all($publicationid, $limit = null){
      if(empty($limit)){
        $limit = get_config("local_social_course", "maxrecordsperquery");
      }
      global $DB;
      $sql = "select c.*, u.username, u.firstname, u.lastname, u.email, u.lastaccess, (select lsl.timecreated 
              from {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as lastaction from {user} u, {sc_comments} c where u.id = c.sc_authorid and 
              sc_publicationid = ? order by c.sc_timecreated desc";
      $rows = $DB->get_records_sql($sql, array($publicationid), null, $limit);
      $comments = self::transform_to_classes($rows);
      return $comments;
    }

    public static function transform_to_classes($rows){
      $comments = array();
      foreach($rows as $row){
        $comment = new local_social_course_comment();
        $comment->id = $row->id;
        $comment->content = $row->sc_content;
        $comment->publicationid = $row->sc_publicationid;
        $comment->timecreated = $row->sc_timecreated;
        $comment->timedeleted = $row->sc_timedeleted;
        $author = new local_social_course_person();
        $author->id = $row->sc_authorid;
        $author->username = $row->username;
        $author->firstname = $row->firstname;
        $author->lastname = $row->lastname;
        $author->email = $row->email;
        $author->lastaccess = $row->lastaccess;
        $author->lastaction = $row->lastaction;
        $comment->author = $author;
        array_push($comments, $comment);
      }
      return $comments;
    }

    public function get(){
      $txt_deleted = get_string("deleted_comment", "local_social_course");
      $comment = new stdClass();
      $comment->id = $this->id;
      $comment->content = $this->timedeleted ? $txt_deleted : $this->content;
      $comment->publicationid = $this->publicationid;
      $comment->author = $this->author;
      $comment->timecreated = $this->timecreated;
      $comment->timedeleted = $this->timedeleted;
      return $comment;
    }
  }