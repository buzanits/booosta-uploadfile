<?php
namespace booosta\uploadfile;

use \booosta\Framework as b;
b::init_module('uploadfile');

class Uploadfile extends \booosta\file\File
{
  use moduletrait_uploadfile;

  protected $filename;        // name of file (seeme.jpg)
  protected $pathname;        // path of file (files/pics/)
  protected $pathfilename;    // full path to file (files/pics/seeme.jpg")
  protected $extension;       // extension of file (jpg)
  protected $rawname;         // filename without extension (seeme)
  protected $origname;        // name of original uploaded file

  protected $valid;           // is it a valid uploadfile
  protected $error;

  public function __construct($postfilename, $path = 'upload/', $preservename = false)
  {
    #\booosta\debug($_FILES);
    parent::__construct();

    if($_FILES[$postfilename]['name'] == '' && $_FILES[$postfilename]['error'] != 0):
      $this->valid = false;
      $this->error .= $_FILES[$postfilename]['error'];
      return;
    endif;

    $this->valid = true;
    if($path == '') $path = 'upload/';
    if(substr($path, -1) != '/') $path .= '/';

    $postfiles = $_FILES[$postfilename];
    if($postfiles['tmp_name'] == 'none' || $postfiles['tmp_name'] == ''):
      $this->valid = false;
      $this->error .= "tmp_name = '{$postfiles['tmp_name']}' ";
      #\booosta\debug("error: $this->error");
      return false;
    endif;

    $tmpfile = array_pop(explode('/', $postfiles['tmp_name']));
    $this->set('pathfilename', $tmpfile . '.' . $this->get_fileextension($postfiles['name']));

    if(move_uploaded_file($postfiles['tmp_name'], "$path$this->filename") == false):
      $this->valid = false;
      $this->error .= "move_uploaded_file {$postfiles['tmp_name']} to $path$this->filename has failed ";
    endif;

    if($preservename === true) $newfile = $path . $postfiles['name'];
    elseif(is_string($preservename) && substr($preservename, -1, 1) == '*') $newfile = $path . uniqid(substr($preservename, 0, -1)) . '.' . $this->extension;
    elseif(is_string($preservename)) $newfile = $path . $preservename;
    else $newfile = $path . uniqid('file_') . '.' . $this->extension;

    copy("$path$this->filename", $newfile);
    if(is_file("$path$this->filename")) unlink("$path$this->filename");

    $this->set('pathfilename', $newfile);
    $this->set('origname', $postfiles['name']);
  }

  public function is_valid() { return $this->valid; }
  public function get_url() { return $this->pathfilename; }
  public function get_filename() { return $this->filename; }
  public function get_extension() { return $this->extension; }
  public function get_origname() { return $this->origname; }
  public function get_error() { return $this->error; }
  public function print_html() { print $this->get_html(); }
  public function destroy() { unlink($this->pathfilename); }

  public function check_extension($extensionmap = null, $command = '/usr/bin/file') 
  {
    $command .= ' ' . escapeshellarg($this->get_url());
    $result = exec($command);
    $result = explode(':', $result);
    $result = trim($result[1]);
    
    if($extensionmap === null)
      $extensionmap = [
        'pdf' => 'PDF document',
        'png' => 'PNG image data',
        'jpg' => 'JPEG image data',
        'jpeg' => 'JPEG image data',
        'gif' => 'GIF image data'
      ];
    
    $expected_string = $extensionmap[strtolower($this->extension)];
    #if(substr_compare($result, $expected_string, 0, strlen($expected_string)) == 0) return true;
    if(strstr($result, $expected_string)) return true;
    return false;  
  }

  public function get_html($linktext = 'File')
  {
    $tag = "<a href='" . $this->get_url() . "'>$linktext</a>";
    return $tag;
  }

  public function set($var, $val) 
  { 
    $this->$var = $val; 

    switch($var):
      case 'filename':
        $this->pathfilename = $this->pathname . "/$val"; 
        $this->extension = $this->get_fileextension($val);
        $this->rawname = $this->get_rawname($val);
        break;
      case 'pathname':
        $this->pathfilename = $val . $this->filename; 
        break;
      case 'pathfilename':
        $tmp = explode('/', $val);
        $this->filename = array_pop($tmp);
        $this->pathname = implode('/', $tmp);
        $this->extension = $this->get_fileextension($this->filename);
        $this->rawname = $this->get_rawname($this->filename);
        break;
      case 'extension':
        $this->filename = $this->rawname . $val;
        $this->pathfilename = $this->pathname . $this->filename;
        break;
      case 'rawname':
        $this->filename = $val . $this->extension;
        $this->pathfilename = $this->pathname . $this->filename;
        break;
    endswitch;
  } // function
} // class
