<?php 
require_once("../locallib.php");
class local_social_course_file extends local_social_course_model{
  protected $publicationid;
  protected $contextid;
  protected $component;
  protected $filearea;
  protected $filepath;
  protected $filename;
  protected $mimetype;
  protected $timecreated;
  protected $timedeleted;
  
  function __construct(){
    $this->component = "local_social_course";
    $this->filearea = "social_course_attachment";
    $this->filepath = "/";
    $this->classname = 'local_social_course_file';
    $this->fillable = ["publicationid","contextid","component","filearea","filepath","filename",
                       "mimetype","timecreated","timedeleted"];
    $this->obtainable = ["publicationid","contextid","component","filearea","filepath","filename",
                         "mimetype","timecreated","timedeleted"];
  }

  public static function store($resource, $courseid, $userid){
    dd("ejejejeje");
    // $context = context_course::instance($courseid);
    // $this->contextid = $context->id; 
  }

  public function save_on_database(){

  }

  public function save_on_disk($resource, $context){
    $storage = get_file_storage();
    $file = array('contextid' => $this->contextid, 'component' => $this->component,
                  'filearea' => $this->filearea, 'itemid' => $resource->id,
                  'filepath' => $this->filepath, 'filename' => $resource->name);
    if(!local_social_course_file_exist($file)){
      $storage->create_file_from_pathname($file, $resource->path);
    }
    $url = get_local_social_course_url($courseid, $publicationid);
  }

}