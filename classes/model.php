<?php 
class local_social_course_model extends local_social_course_core{
  protected $classname;
  protected $fillable;
  protected $obtainable;
  const MUTATOR_FUNCTION_PREFIX = "set_";
  const ACCESOR_FUNCTION_PREFIX = "get_";

  function __construct(){
    $this->fillable = [];
    $this->obtainable = [];
  }

  public function fill($params){
    if(gettype($params) != 'array'){
      return null;
    }
    foreach($this->fillable as $property){
      $property_exist = isset($params[$property]); 
      if($property_exist){
        $method = self::MUTATOR_FUNCTION_PREFIX . $property; 
        if(method_exists($this->classname, $method)){
          $this->$property = $this->classname::$method($params[$property]);
        }else{
          $this->$property = $params[$property];
        }
      }
    }
  }

  public function get(){
    $response = new stdClass();
    foreach($this->obtainable as $property){
      $property_exist = isset($this->$property); 
      if($property_exist){
        $method = self::ACCESOR_FUNCTION_PREFIX . $property; 
        if(method_exists($this->classname, $method)){
          $response->$property = $this->classname::$method($this->$property);
        }else{
          $response->$property = $this->$property;
        }
      }
    }
    return $response;
  }

  public function getProperty($property_name){
    $response = null;
    $property_exist = isset($this->$property_name); 
    if($property_exist){
      $method = self::ACCESOR_FUNCTION_PREFIX . $property_name; 
      if(method_exists($this->classname, $method)){
        $response = $this->classname::$method($this->$property_name);
      }else{
       $response = $this->$property_name;
      }
    }
    return $response;
  }
}