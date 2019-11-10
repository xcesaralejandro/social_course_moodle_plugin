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