<?php

Web2All_Manager_Main::loadClass('Web2All_File_Object');

/**
 * Web2All File class
 * 
 * Manage a file
 * 
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2008 Web2All BV
 * @since 2008-08-26
 */
class Web2All_File_File extends Web2All_File_Object {
  
  /**
   * constructor
   *
   * @param Web2All_Manager_Main $web2all
   * @param string $file
   * @param Web2All_File_IProvider $provider
   */
  public function __construct(Web2All_Manager_Main $web2all,$file,$provider=null) {
    parent::__construct($web2all,$file,$provider);
    
    // check if we may manipulate it
    if (!$this->provider->isAllowedDir(dirname($this->full_path))) {
      // we may not manage directories here
      throw new Exception('Web2All_File_File: may not manage this ('.$this->full_path.') file');
    }
  }
  
  /**
   * get the filename (no path) of this file
   *
   * @return string
   */
  public function getFilename()
  {
    return $this->getName();
  }
  
  /**
   * get the base filename (no path and extension) of this file
   *
   * @return string
   */
  public function getBaseFilename()
  {
    return pathinfo($this->full_path,PATHINFO_FILENAME);
  }
  
  /**
   * return the file extension
   *
   * @return string
   */
  public function getExtension()
  {
    return pathinfo($this->full_path,PATHINFO_EXTENSION);
  }
  
  
  /**
   * Check if this file exists on the filesystem
   *
   * @return boolean
   */
  public function exists()
  {
    return $this->provider->exists($this);
  }
  
  
  /**
   * Recursively delete this file
   *
   * @return boolean
   */
  public function delete() {
    return $this->provider->delete($this);
  }
  
  /**
   * Magic tostring method gets the filename (no path) of this file
   *
   * @return string
   */
  public function __toString()
  {
    return basename($this->full_path);
  }
  
}

?>