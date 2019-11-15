<?php 
class local_social_course_recipient{
  public $id;
  public $recipientid;
  public $publicationid;
  public $timecreated;
  public $timedeleted;

  public function __construct(){

  }

  public function get(){
    $recipient = new stdClass();
    $recipient->id = $this->id;
    $recipient->recipientid = $this->recipientid;
    $recipient->publicationid = $this->publicationid;
    $recipient->timecreated = $this->timecreated;
    $recipient->timedeleted = $this->timedeleted;
    return $recipient;
  }

  public static function all($publicationid){
    global $DB;
    $sql = "select u.id, u.username, u.firstname, u.lastname, u.email, u.lastaccess,
            (select lsl.timecreated from {logstore_standard_log} lsl where lsl.userid = u.id 
            order by lsl.timecreated desc limit 1) as lastaction from {sc_recipients} r, 
            {user} u where u.id = sc_to and r.sc_publicationid = ? and r.sc_timedeleted IS NULL";
    $rows = $DB->get_records_sql($sql, array($publicationid));
    $recipients = self::transform_to_recipients($rows);
    return $recipients;
  }

  public static function transform_to_recipients($rows){
    $recipients = array();
    foreach($rows as $row){
      $recipient = new local_social_course_person();
      $recipient->id = $row->id;
      $recipient->username = $row->username;
      $recipient->firstname = $row->firstname;
      $recipient->lastname = $row->lastname;
      $recipient->email = $row->email;
      $recipient->lastaccess = $row->lastaccess;
      $recipient->lastaction = $row->lastaction;
      array_push($recipients, $recipient);
    }
    return $recipients;
  }

  public function save(){
    if(!self::validate_properties()){
      return null;
    }
    global $DB;
    $now = time();
    $recipient = new stdClass();
    $recipient->sc_publicationid = $this->publicationid; 
    $recipient->sc_to = $this->recipientid; 
    $recipient->sc_timecreated = $now;
    $id = $DB->insert_record("sc_recipients", $recipient, true);
    $this->timecreated = $now;
    $this->id = $id;
    return $recipient;
  }

  private function validate_properties(){
    $valid = false;
    if(!empty($this->recipientid) && !empty($this->publicationid)){
        $valid = true;
    }
    return $valid;
  }
}