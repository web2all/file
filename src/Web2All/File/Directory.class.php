<?php

Web2All_Manager_Main::loadClass('Web2All_File_Object');

/**
 * Web2All Directory class
 * 
 * Manage directories
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-08-11
 */
class Web2All_File_Directory extends Web2All_File_Object implements Iterator,Countable {
  
  protected $content=null;
  
  protected $advancedmode;
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param string $dir
   * @param boolean $advancedmode  [optional option, when true then content 
   *                                is returned as File/Directory objects]
   * @param Web2All_File_IProvider $provider
   */
  public function __construct(Web2All_Manager_Main $web2all,$dir,$advancedmode=false,$provider=null) {
    if (!$dir) {
      throw new Exception('Web2All_File_Directory: no directory given');
    }
    parent::__construct($web2all,$dir,$provider);
    
    // check if we may manipulate it
    if (!$this->provider->isAllowedDir($this->full_path)) {
      // we may not manage directories here
      throw new Exception('Web2All_File_Directory: may not manage this ('.$this->full_path.') directory');
    }
    $this->advancedmode=$advancedmode;
  }
  
  /**
   * Set the advanced mode (when true, Web2All_File objects will be returned
   * as directory content).
   * 
   * @param boolean $advanced
   */
  public function setAdvancedMode($advanced)
  {
    $this->advancedmode=$advanced;
  }
  
  /**
   * Get the advanced mode (when true, Web2All_File objects will be returned
   * as directory content).
   * 
   * @return boolean
   */
  public function getAdvancedMode()
  {
    return $this->advancedmode;
  }
  
  /**
   * Read the directory content and return all files and
   * directories as an array.
   * 
   * The values of the array are filename only, not the
   * full path. (unless advancedmode is true)
   *
   * @return array
   */
  public function getContent()
  {
    // reset content
    $this->content=array();
    if ($this->advancedmode) {
      $this->content=$this->provider->getDirectoryListingFull($this);
    }else{
      $this->content=$this->provider->getDirectoryListingSimple($this);
    }
    return $this->content;
  }
  
  /**
   * return name this directory
   *
   * The top level name, so /this/is/mydir/ returns mydir
   *
   * @return string
   */
  public function getName()
  {
    $parts=explode($this->dirsep,$this->full_path);
    $name=array_pop($parts);
    if($name){
      return $name;
    }
    return array_pop($parts);
  }
  
  /**
   * Return the path of this directory, relative to the given basepath
   * 
   * returns false when not in the basepath at all
   *
   * @param string $basepath
   * @return mixed  false or string
   */
  public function getRelativePath($basepath)
  {
    if (!$basepath) {
      throw new Exception('Web2All_File_Directory->getRelativePath: no basepath given');
    }
    
    if ($basepath[strlen($basepath)-1]!=$this->dirsep) {
      $basepath.=$this->dirsep;
    }
    $pos=strpos($this->full_path,$basepath);
    if ($pos===0) {
      return substr($this->full_path,strlen($basepath));
    }
    return false;
  }
  
  
  /**
   * Check if this directory exists on the filesystem
   *
   * @return boolean
   */
  public function exists()
  {
    return $this->provider->exists($this);
  }
  
  
  /**
   * Recursively delete this directory
   *
   * @return boolean
   */
  public function delete() {
    return $this->provider->delete($this);
  }
  
  /**
   * Create a new directory
   *
   * @return boolean
   */
  public function create()
  {
    return $this->provider->createDirectory($this);
  }
  
  
  /**
   * equal to reset() on an array
   * 
   * Iterator implementation
   */
  public function rewind() {
    if (is_null($this->content)) {
      $this->getContent();
    }
    reset($this->content);
  }

  /**
   * equal to current() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function current() {
    if (is_null($this->content)) {
      $this->getContent();
    }
    $var = current($this->content);
    return $var;
  }

  /**
   * equal to key() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function key() {
    if (is_null($this->content)) {
      $this->getContent();
    }
    $var = key($this->content);
    return $var;
  }

  /**
   * equal to next() on an array
   * 
   * Iterator implementation
   * 
   * @return mixed
   */
  public function next() {
    if (is_null($this->content)) {
      $this->getContent();
    }
    $var = next($this->content);
    return $var;
  }

  /**
   * check if the end of array is reached
   * 
   * Iterator implementation
   * 
   * @return boolean
   */
  public function valid() {
    $var = $this->current() !== false;
    return $var;
  }
  
  
  /**
   * Retrieve number of files and directories in this directory
   * 
   * Countable implementation
   * 
   * @return int
   */
  public function count() {
    if (is_null($this->content)) {
      $this->getContent();
    }
    return count($this->content);
  }
  
  /**
   * Sort the content alphabetically a-z
   *
   */
  public function sort()
  {
    if (is_null($this->content)) {
      $this->getContent();
    }
    sort($this->content);
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
    return true;
  }
  
}

?>