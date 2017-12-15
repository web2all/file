<?php

Web2All_Manager_Main::loadClass('Web2All_File_IProvider');

/**
 * Web2All File Provider for UnixFileSystem class
 * 
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-11-11
 */
class Web2All_File_Provider_UnixFileSystem extends Web2All_Manager_Plugin implements Web2All_File_IProvider {
  
  /**
   * Directory separator
   * 
   * (default = /)
   *
   * @var string
   */
  protected $dirsep;
  
  /**
   * Config array
   *
   * @var array
   */
  protected $config;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param string $configkey  which configkey to use (defaults to 'Web2All_File')
   */
  public function __construct(Web2All_Manager_Main $web2all,$configkey=null) {
    parent::__construct($web2all);
    
    if(is_null($configkey)){
      $configkey='Web2All_File';
    }
    
    // load config
    $defaultconfig=array(
      'allowed_paths' => array('/var/data'),
      'directory_separator' => '/',
      'files_must_exist' => true,
      'dirs_must_exist' => false,
      'debuglevel' => 3
    );
    
    $this->config=$this->Web2All->Config->makeConfig($configkey,$defaultconfig);
    
    $this->dirsep=$this->config['directory_separator'];
    
  }
  
  /**
   * Check if the object actually exists on the filesystem
   *
   * @param Web2All_File_Object $object
   * @return boolean
   */
  public function exists($object)
  {
    if($object->isDirectory()){
      return is_dir($object->getPath());
    }else{
      return file_exists($object->getPath());
    }
  }
  
  /**
   * Delete this object (recursively in case of directory)
   *
   * @param Web2All_File_Object $object
   * @return boolean
   */
  public function delete($object){
    
    if($this->Web2All->DebugLevel >= $this->config['debuglevel']) {
      $this->Web2All->DebugLog('Web2All_File_Provider_UnixFileSystem->delete: attemting to delete '.$object->getPath());
    }
    
    if (!$this->exists($object)) {
      // cannot delete nonexistent directory
      return false;
    }
    
    // script is evil. so lets at least add a simple check.
    if (!$object->getPath() || $object->getPath()=='/') {
      // we never remove the entire server
      trigger_error('Web2All_File_Provider_UnixFileSystem->delete: trying to remove server root, aborting.');
      return false;
    }
    
    $success=false;
    if($object->isDirectory()){
      if(!$object->getAdvancedMode()){
        // this directory object is not in advanced mode.
        $object=clone $object;// clone, so we don't change original object
        $object->setAdvancedMode(true);
      }
      $object->getContent();
      // and now delete everything
      foreach($object as $subobject) {
        $subobject->delete();
      }
      $success=rmdir($object->getPath());
    }else{
      $success=unlink($object->getPath());
    }
    
    return $success; 
  }
  
  /**
   * Rename this object
   *
   * @param Web2All_File_Object $object
   * @param string $newname
   * @return boolean
   */
  public function rename($object,$newname)
  {
    if($this->Web2All->DebugLevel >= $this->config['debuglevel']) {
      $this->Web2All->DebugLog('Web2All_File_Provider_UnixFileSystem->rename: attemting to rename '.$object->getPath().' to '.$newname);
    }
    
    if (!$this->exists($object)) {
      // cannot rename nonexistent object
      return false;
    }
    
    // script is evil. so lets at least add a simple check.
    if (!$object->getPath() || $object->getPath()=='/') {
      // bad situation
      trigger_error('Web2All_File_Provider_UnixFileSystem->rename: trying to rename server root, aborting.');
      return false;
    }
    
    // note: rename will overwrite target if it exists
    $success=rename($object->getPath(),$object->getDirectory().$this->dirsep.$newname);
    
    return $success;
  }
  
  /**
   * Create the directory
   *
   * @param Web2All_File_Directory $directory
   * @return boolean
   */
  public function createDirectory($directory)
  {
    if($this->Web2All->DebugLevel >= $this->config['debuglevel']) {
      $this->Web2All->DebugLog('Web2All_File_Directory->create: attemting to create '.$directory->getPath());
    }
    // first find out from which level in the dir tree, the directories exist
    // and from that point, start creating them;
    // mkdir does support a recursive mode, but we want more control
    $path=$directory->getPath();
    $dir_stack=array();
    while (!is_dir($path) && $path!=$this->dirsep && $path!='') {
      $path_parts = pathinfo($path);
      $dir_stack[]=$path_parts['basename'];
      $path=$path_parts['dirname'];
    }
    // now $path is the root dir from where we start creating
    // we should check if we are allowed to create directories here
    // turn it into a fully expanded path (possible relative paths get
    // resolved)
    $path=realpath($path);
    // if the path was empty, it will now be the current working directory

    if (!$this->isAllowedDir($path)) {
      // we may not create directories here
      if($this->Web2All->DebugLevel >= $this->config['debuglevel']) {
        $this->Web2All->DebugLog('Web2All_File_Directory->create: not allowed by config to create '.$directory->getPath());
      }
      return false;
    }

    while (!is_null($subdir=array_pop($dir_stack))) {
      $path.=$this->dirsep.$subdir;
      if (!mkdir($path)) {
        // maybe undo creation of sub levels?
        return false;
      }
    }
    return true;
  }
  
  /**
   * List the directory, return only filenames
   *
   * @param Web2All_File_Directory $directory
   * @return string[]
   */
  public function getDirectoryListingSimple($directory)
  {
    return $this->getDirectoryListing($directory,false);
  }
  
  /**
   * List the directory, return file object's
   *
   * @param Web2All_File_Directory $directory
   * @return Web2All_File_Object[]
   */
  public function getDirectoryListingFull($directory)
  {
    return $this->getDirectoryListing($directory,true);
  }
  
  /**
   * List the directory
   *
   * @param Web2All_File_Directory $directory
   * @param boolean $advancedmode
   * @return array
   */
  protected function getDirectoryListing($directory,$advancedmode)
  {
    $list=array();
    if ($dh = opendir($directory->getPath()))
    {
      while (($sf = readdir($dh)) !== false)
      {
        if ($sf == '.' || $sf == '..')
        {
          // skip the current and parent dir indicators
          continue;
        }
        if ($advancedmode) {
          $path=$directory->getPath().$this->dirsep.$sf;
          if (is_dir($path)) {
            $list[]=$this->Web2All->Plugin->Web2All_File_Directory($path,true,$this);
          }else{
            $list[]=$this->Web2All->Plugin->Web2All_File_File($path,$this);
          }
        }else{
          $list[]=$sf;
        }
      }
      closedir($dh);
    }
    return $list;
  }
  
  /**
   * Check if the given path is in an allowed tree
   *
   * @param string $path
   * @return boolean
   */
  public function isAllowedDir($path)
  {
    foreach ($this->config['allowed_paths'] as $allowed_dir) {
      if (strpos($path,$allowed_dir)===0) {
        // the path starts with this allowed apth, thats good
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * Get the directory separator
   *
   * @return string
   */
  public function getDirectorySeparator()
  {
    return $this->dirsep;
  }
  
  /**
   * Determines if Files must actually exists
   *
   * @return boolean
   */
  public function filesMustExist()
  {
    return $this->config['files_must_exist'];
  }
  
  /**
   * Determines if Directories must actually exists
   *
   * @return boolean
   */
  public function directoriesMustExist()
  {
    return $this->config['dirs_must_exist'];
  }
}
?>