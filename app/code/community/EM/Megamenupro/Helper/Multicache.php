<?php
class EM_Megamenupro_Helper_Multicache extends Mage_Core_Helper_Abstract
{
	public function __construct()
	{
		$this->cache_dir	=	Mage::getBaseDir('var').DS.'cache'.DS.'megamenupro'.DS;		

			if(!$this->cache_dir)
			{
				die('Cannot load config cache filearray');exit;
			}
			@mkdir($this->cache_dir,0777,true);
			$lib_cache	=	Mage::helper('megamenupro/cache');
	}
	
	public function set($key=false,$value=false,$timeout=60)
	{
		$timeout = intval($timeout);
		if(!$key) return false;
		if($timeout<1) return false;

			$plit	=	explode("_",$key);
			$store	=	$plit[1];
			$catid	=	$plit[2];
			@mkdir($this->cache_dir.$store.'/',0777,true);
			@mkdir($this->cache_dir.$store.'/'.$catid.'/',0777,true);

			$array['datacreated']	=	time();
			$array['timeout']		=	$timeout;
			$array['datavalue']		=	$value;
			$lib_cache	=	Mage::helper('megamenupro/cache');
			return $lib_cache->make($array,$this->cache_dir.$store.'/'.$catid.'/'.$key.'.php');
		
	}
	
	public function get($key=false)
	{	
		if(!$key) return false;
			$plit	=	explode("_",$key);
			$store	=	$plit[1];
			$catid	=	$plit[2];

			$lib_cache	=	Mage::helper('megamenupro/cache');
			$data	=	$lib_cache->load($this->cache_dir.$store.'/'.$catid.'/'.$key.'.php');

			if(!$data) return false;
			if(intval($data['datacreated'])	+ intval($data['timeout']) < time())
			{
				@unlink($this->cache_dir.$store.'/'.$catid.'/'.$key.'.php');
				return false;
			}
			return $data['datavalue'];
		
	}
	
	public function delete($key)
	{
		if(!$key) return false;
		
			@unlink($this->cache_dir.$key.'.php');
			return true;
		
	}
	
	public function clear()
	{
		
			$this->recursiveDelete($this->cache_dir);
			return true;
		
	}
	
    private function recursiveDelete($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                $this->recursiveDelete($path);
            }
            return true;
        }
    }
	
}