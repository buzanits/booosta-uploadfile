<?php
namespace booosta\uploadfile;

\booosta\Framework::add_module_trait('webapp', 'uploadfile\webapp');

trait webapp
{
  protected function uploaded_file($name, $dir = 'upload/', $preservename = false)
  {
    $upfile = $this->makeInstance('uploadfile', $name, $dir, $preservename);
    if(!$upfile->is_valid()) return null;
    return $upfile->get_filename();
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
