<?php 
  class local_social_course_comment{

    public $id;
    public $content;
    public $publicationid;
    public $author;
    public $timecreated;
    public $timedeleted;

    public static function get_all_from_publication($publicationid, $limit = null){
      $records = self::get_comments($publicationid, $limit = null);
      $comments = array();
      foreach($records as $record){
        $comment = new local_social_course_comment();
        $comment->id = $record->id;
        $comment->content = $record->sc_content;
        $comment->publicationid = $record->sc_publicationid;
        $comment->timecreated = $record->sc_timecreated;
        $comment->timedeleted = $record->sc_timedeleted;
        $author = new local_social_course_author();
        $author->id = $record->sc_authorid;
        $author->username = $record->username;
        $author->firstname = $record->firstname;
        $author->lastname = $record->lastname;
        $author->email = $record->email;
        $author->lastaccess = $record->lastaccess;
        $comment->author = $author->get();
        array_push($comments, $comment->get());
      }
      return $comments;
    }

    public static function get_comments($publicationid, $limit = null){
      if(empty($limit)){
        $limit = get_config("local_social_course", "maxrecordsperquery");
      }
      global $DB;
      $sql = "select c.*, u.username, u.firstname, u.lastname, u.email, u.lastaccess (select lsl.timecreated 
              from {logstore_standard_log} lsl where lsl.userid = u.id order by lsl.timecreated desc limit 1) 
              as author_last_action from {user} u, {sc_comments} c where u.id = c.sc_authorid and 
              sc_publicationid = ? order by c.timecreated desc";
      $comments = $DB->get_records_sql($sql, array($publicationid), null, $limit);
      $comments = array_values($comments);
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