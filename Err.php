<?php

namespace Log;

class Err extends Log {
	static public function fatal(\Error $e){
		$error = $e->getMessage().' '.$e->getFile().'('.$e->getLine().")\n".$e->getTraceAsString();
		if($prev_e = $e->getPrevious()){
			$error .= "\nPrevious: ".$prev_e->getFile().'('.$prev_e->getLine().")\n".$prev_e->getTraceAsString();
		}
		
		self::err($error, self::ERR_FATAL, 1);
	}
}