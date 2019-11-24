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
  }

  public function get_resource($id){
    $file = self::find($id);
    if(!$file){
      return false;
    }
    $resource = self::to_resource();
    return $resource;
  }

  private function to_resource(){
    $resourse = new local_social_course_resource();
    $resource->id = $this->id;
    $resource->name = $this->filename;
    $resource->path = self::get_url();
    $resource->type = $this->mimetype;
    return $resource;
  }

  public function get_file ($id){
    $file = self::find($id);
    return $file;
  }

  private function find($id){
    global $DB;
    $sql = "select * from {sc_files} where id = ? and sc_timedeleted IS NULL";
    $row = $DB->get_record_sql($sql, array($id));
    if(!$row){
      return false;
    }
    $file = self::fill_from($row);
    return $file;
  }

  private function fill_from($row){
    $this->id = $row->id;
    $this->publicationid = $row->sc_publicationid;
    $this->userid = $row->sc_userid;
    $this->contextid = $row->sc_contextid;
    $this->component = $row->sc_component;
    $this->filearea = $row->sc_filearea;
    $this->filepath = $row->sc_filepath;
    $this->filename = $row->sc_filename;
    $this->mimetype = $row->sc_mimetype;
    $this->timecreated = $row->sc_timecreated;
    $this->timedeleted = $row->sc_timedeleted;
    $file = self::search_file();
    return $file;
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
    $resource = self::to_resource();
    return $resource;
  }

  private function save_in_database($resource){
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

  private function save_in_disk($resource){
    if(!self::file_exist()){
      $file = self::get();
      $storage = get_file_storage();
      $file = $storage->create_file_from_pathname($file, $resource->path);
    }
    $url = self::get_url();
    return $url;
  }

  private function file_exist(){
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

  private function get_url($forcedownload = false) {
    $file = self::search_file();
    if (!$file){
      return false;
    }
    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                           $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                                           $forcedownload);
    $url = (string) $url;
    return $url;
  }

  private function search_file(){
    $storage = get_file_storage();
    $file = $storage->get_area_files($this->contextid, $this->component, $this->filearea,
                                     $this->id, $sort = false, $includedirs = false);
    if (empty($file)){
      return false;
    }
    return array_shift($file);
  }

}