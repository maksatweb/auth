<?php namespace TT\Libraries\Session\Drivers;



use SessionHandlerInterface;



class SessionFileDriver implements SessionHandlerInterface
{


  private $save_path;

  private $file_path;





  public function open($save_path,$name):Bool
  {
    if(!is_dir($save_path))
    {
      if (!mkdir($save_path,0700,true))
      {
        throw new \Exception("Session: Configured save path [{$save_path}] is not a directory, doesn't exist or cannot be created.");
      }
    }

    $this->save_path = $save_path ;

    $this->file_path = $save_path.'/session_'.md5($_SERVER['REMOTE_ADDR']);

    return true;
  }


  public function close():Bool
  {
    return $this->gc(ini_get('session.gc_maxlifetime'));
  }



  public function read($id)
  {
    if(!file_exists($this->file_path.$id))
    {
      $this->newFile($id);
    }

    return ''.file_get_contents($this->file_path.$id);

  }



  public function write($id,$session_data):Bool
  {
    if(!file_exists($this->file_path.$id))
    {
      $this->newFile($id);
    }

    return file_put_contents($this->file_path.$id, $session_data ,LOCK_EX);
  }




  public function destroy($id):Bool
  {
    if(file_exists($this->file_path.$id))
    {
      return unlink($this->file_path.$id);
    }
    return true;
  }



  public function gc($maxlifetime):Bool
  {
    foreach (glob("{$this->save_path}/session_*") as $file)
    {
      if((filemtime($file) + $maxlifetime) < time())
      {
        @unlink($file);
      }
    }
    return true;
  }


  private function newFile($session_id)
  {
    if(!file_exists($this->file_path.$session_id))
    {
      $file = fopen($this->file_path.$session_id,'c+b');
      flock($file,LOCK_EX);
      chmod($this->file_path.$session_id,0600);
      fclose($file);
    }
  }





}
