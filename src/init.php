<?php
namespace booosta\uploadfile;

\booosta\Framework::add_module_trait('webapp', 'uploadfile\webapp');

trait webapp
{
  protected function uploaded_file($name, $dir = 'upload/', $preservename = false)
  {
    $obj = $this->uploaded_file_obj($name, $dir, $preservename);
    return $obj->get_filename();
  }

  protected function uploaded_file_origname($name, $dir = 'upload/', $preservename = false)
  {
    $obj = $this->uploaded_file_obj($name, $dir, $preservename);
    if(!is_object($obj)) return null;

    return ['file' => $obj->get_filename(), 'origname' => $obj->get_origname()];
  }

  protected function uploaded_file_obj($name, $dir = 'upload/', $preservename = false)
  {
    $upfile = $this->makeInstance('uploadfile', $name, $dir, $preservename);
    if(!$upfile->is_valid()) return null;
    return $upfile;
  }

  protected function replace_uploaded_file($field, $name, $dir = 'upload/', $preservename = false, $obj = null)
  {
    if(is_object($obj)) $field_content = $obj->get($field);
    else $field_content = $this->get_data($field);

    $upfile = $this->makeInstance('uploadfile', $name, $dir, $preservename);
    if($upfile->is_valid()):
      unlink($dir . $field_content);
      $obj->set($field, $upfile->get_filename());
      return $upfile->get_filename();
    else:
      return $field_content;
    endif;
  }

  protected function remove_uploaded_file($field, $dir = 'upload/', $obj = null)
  {
    if(is_object($obj)):
      $field_content = $obj->get($field);
      $obj->set($field, null);
    else:
      $field_content = $this->get_data($field);
    endif;

    unlink($dir . $field_content);
    return null;
  }
}
