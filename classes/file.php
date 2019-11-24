<?php 
class local_social_course_file{
  protected $id;
  protected $publicationid;
  protected $userid;
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
    $this->timecreated = time();
    // $this->classname = 'local_social_course_file';
    // $this->fillable = ["id", "userid","publicationid","contextid","component","filearea","filepath","filename",
    //                    "mimetype","timecreated","timedeleted"];
    // $this->obtainable = ["publicationid","contextid","component","filearea","filepath","filename",
    //                      "mimetype","timecreated","timedeleted"];
  }

  public function store($resource, $courseid, $userid){
    $context = context_course::instance($courseid);
    $this->contextid = $context->id;
    $this->userid = $userid;
    $this->filename = $resource->name;
    $this->mimetype = $resource->type;
    $this->id = self::save_in_database($resource);
    if($this->id){
      self::save_in_disk($resource);
    }else{
      return false;
    }
  }

  public function save_in_database($resource){
    global $DB;
    $file = new stdClass();
    $file->sc_publicationid = $this->publicationid;
    $file->sc_contextid = $this->contextid;
    $file->sc_userid = $this->userid;
    $file->sc_component = $this->component;
    $file->sc_filearea = $this->filearea;
    $file->sc_filepath = $this->filepath;
    $file->sc_filename = $this->filename;
    $file->sc_mimetype = $this->mimetype;
    $file->sc_timecreated = $this->timecreated;
    $file->sc_timedeleted = $this->timedeleted;
    $id = $DB->insert_record("sc_files", $file, true);
    return $id;
  }

  public function save_in_disk($resource){
    if(!self::file_exist()){
      $file = self::get();
      $storage = get_file_storage();
      $file = $storage->create_file_from_pathname($file, $resource->path);
    }
    $url = get_local_social_course_url($courseid, $publicationid);
  }

  public function file_exist(){
    $storage = get_file_storage();
    $exist = $storage->file_exists($this->contextid, $this->component, $this->filearea, $this->itemid, 
                                   $this->filepath, $this->filename); 
    return $exist;
  }

  public function get(){
    $file = ["contextid" => $this->contextid, "component" => $this->component, "filearea" => $this->filearea,
             "itemid" => $this->id, "filepath" => $this->filepath, "filename" => $this->filename];
    return $file;
  }

}