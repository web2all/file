<?php

/**
 * Web2All File Provider interface
 *
 * This interface needs to be implemented by all 
 * Filesystem providers.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2010 Web2All BV
 * @since 2010-11-11 
 */
interface Web2All_File_IProvider {
  
  /**
   * Check if the object actually exists on the filesystem
   *
   * @param Web2All_File_Object $object
   * @return boolean
   */
  public function exists($object);
  
  /**
   * Delete this object (recursively in case of directory)
   *
   * @param Web2All_File_Object $object
   * @return boolean
   */
  public function delete($object);
  
  /**
   * Rename this object
   *
   * @param Web2All_File_Object $object
   * @param string $newname
   * @return boolean
   */
  public function rename($object,$newname);
  
  /**
   * Create the directory
   *
   * @param Web2All_File_Directory $directory
   * @return boolean
   */
  public function createDirectory($directory);
  
  /**
   * List the directory, return only filenames
   *
   * @param Web2All_File_Directory $directory
   * @return string[]
   */
  public function getDirectoryListingSimple($directory);
  
  /**
   * List the directory, return file object's
   *
   * @param Web2All_File_Directory $directory
   * @return Web2All_File_Object[]
   */
  public function getDirectoryListingFull($directory);
  
  /**
   * Check if the given path is in an allowed tree
   *
   * @param string $path
   * @return boolean
   */
  public function isAllowedDir($path);
  
  /**
   * Get the directory separator
   *
   * @return string
   */
  public function getDirectorySeparator();
  
  /**
   * Determines if Files must actually exists
   *
   * @return boolean
   */
  public function filesMustExist();
  
  /**
   * Determines if Directories must actually exists
   *
   * @return boolean
   */
  public function directoriesMustExist();
  
}

?>