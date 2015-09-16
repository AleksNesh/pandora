<?php
class EM_megamenupro_Helper_Cache extends Mage_Core_Helper_Abstract
{	
	private function encode_quotes($str){
		if(get_magic_quotes_runtime() || get_magic_quotes_gpc()){
			$str = stripcslashes($str);
		}
		$str	=	str_replace("\'","'",$str);
		$str	=	addcslashes($str,'\'');
		return $str;
	}
	
	private function toStr($arr){
		if(!is_array($arr)) return false;
		$str	=	'array('.chr(13);
		foreach($arr as $key=>$value){
			if(!is_array($value)){
				if(!$key) $zero = true;
				if(!$zero){
					$str	.=	'\''.$key.'\''.'=>'.'\''.$this->encode_quotes($value).'\''.','.chr(13);	
				}else{
					$str	.=	'\''.$this->encode_quotes($value).'\''.','.chr(13);	
				}
			}else{
				$str	.=	'\''.$key.'\''.'=>'.$this->toStr($value).','.chr(13);
			}
		}
		$str	.=	chr(13).')';
		return $str;
	}
	
	public function make($arr,$filename,$valname='data',$addtime=false)
	{
		if(is_array($arr) && isset($arr))
		{	
			if($addtime==true) $arr['time_cache']	=	time();
			
			if($f = @fopen($filename, 'wb'))
			{
				if(@fwrite($f, '<?php $'.$valname.'='.$this->toStr($arr).';'.chr(13).' ?>'))
				{
					if(@fclose($f))
					{
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public function load($filename,$valname='data')	
	{		
		if(!@include($filename)) return false;
		$arr = $$valname;
		//echo '<pre>';print_r($arr);exit;		
		return $arr;
	}
}