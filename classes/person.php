<?php 
class local_social_course_person{

  public $id;
  public $username;
  public $firstname;
  public $lastname;
  public $email;
  public $lastaccess;
  public $lastaction;

  public function get(){
    $author = new stdClass();
    $author->id = $this->id;
    $author->username = $this->username;
    $author->firstname = $this->firstname;
    $author->lastname = $this->lastname;
    $author->email = $this->email;
    $author->lastaccess = $this->lastaccess;
    $author->lastaction = $this->lastaction;
    return $author;
  }
}