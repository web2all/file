<?php
/**
 * Web2All File Object class
 * 
 * This is the base class for all filesystem objects
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-11-11
 */
class Web2All_File_Object extends Web2All_Manager_Plugin {
  
  /**
   * The full file (or directory) path
   *
   * @var string
   */
  protected $full_path;
  
  /**
   * Directory separator
   * 
   * (default = /)
   *
   * @var string
   */
  protected $dirsep;
  
  /**
   * Attributes object (or null)
   *
   * @var Web2All_File_Attributes
   */
  protected $attributes=null;
  
  /**
   * Filesystem provider
   *
   * @var Web2All_File_IProvider
   */
  protected $provider;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param string $file
   * @param Web2All_File_IProvider $provider
   */
  public function __construct(Web2All_Manager_Main $web2all,$file,$provider=null) {
    parent::__construct($web2all);
    
    if (!$file) {
      throw new Exception('Web2All_File_Object: no file given');
    }
    $this->full_path=$file;
    
    if(is_null($provider)){
      // no provider, use the default one
      $this->provider=$this->Web2All->PluginGlobal->Web2All_File_Provider_UnixFileSystem();
    }else{
      $this->provider=$provider;
    }
    
    $this->dirsep=$this->provider->getDirectorySeparator();
    
    if($this->isDirectory()){
      if($this->provider->directoriesMustExist()){
        if (!$this->provider->exists($this)) {
          throw new Exception('Web2All_File_Object: directory does not exist ('.$this->full_path.')');
        }
      }
    }else{
      if($this->provider->filesMustExist()){
        if (!$this->provider->exists($this)) {
          throw new Exception('Web2All_File_Object: file does not exist ('.$this->full_path.')');
        }
      }
    }
  }
  
  /**
   * get the filename (no path) of this file
   *
   * @return string
   */
  public function getName()
  {
    return basename($this->full_path);
  }
  
  /**
   * return the (parent) directory that contains this file
   *
   * @return string
   */
  public function getDirectory()
  {
    return dirname($this->full_path);
  }
  
  /**
   * return the full path of this file
   *
   * @return string
   */
  public function getPath()
  {
    return $this->full_path;
  }
  
  /**
   * return the file attributes
   *
   * @return Web2All_File_Attributes
   */
  public function getAttributes()
  {
    return $this->attributes;
  }
  
  /**
   * Set the file attributes
   *
   * @param $attrs Web2All_File_Attributes
   */
  public function setAttributes($attrs)
  {
    $this->attributes=$attrs;
  }
  
  /**
   * Check if this object is a directory
   * 
   * This is a convienience method which exists both in the
   * File and Directory class.
   *
   * @return boolean
   */
  public function isDirectory()
  {
    return false;
  }
  
  /**
   * rename object
   *
   * @param string $newname
   * @return boolean
   */
  public function rename($newname) {
    return $this->provider->rename($this,$newname);
  }
  
}

?>