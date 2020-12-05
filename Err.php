<?php

namespace Log;

class Err extends Log {
	static public function fatal(\Error $e){
		$error = self::format($e);
		if($prev_e = $e->getPrevious()){
			$error .= "\nPrevious error: ".self::format($prev_e);
		}
		
		self::err($error, self::ERR_FATAL, 1);
	}
	
	static private function format(\Error $e): string{
		return $e->getMessage().' '.$e->getFile().'('.$e->getLine().")\n".$e->getTraceAsString();
	}
}