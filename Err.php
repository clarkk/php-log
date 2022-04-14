<?php

namespace Log;

class Err extends Log {
	static public function fatal(\Throwable $e, string $name=self::ERR_FATAL): string{
		$error = self::format($e);
		if($prev_e = $e->getPrevious()){
			$error .= "\nPrevious error: ".self::format($prev_e);
		}
		
		self::err($name, $error);
		
		return $error;
	}
	
	static public function format(\Throwable $e): string{
		return self::trace_format($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
	}
	
	static public function catch_all(\Throwable $e){
		if(!headers_sent()){
			header('Content-Type: text/plain');
		}
		
		$error = self::fatal($e);
		
		if(self::is_verbose()){
			echo $error;
		}
		//	Show less information in production mode
		else{
			echo 'Error: '.get_class($e);
		}
	}
}