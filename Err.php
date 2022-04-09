<?php

namespace Log;

class Err extends Log {
	static public function fatal(\Throwable $e): string{
		$error = self::format($e);
		if($prev_e = $e->getPrevious()){
			$error .= "\nPrevious error: ".self::format($prev_e);
		}
		
		self::err($error, self::ERR_FATAL);
		
		return $error;
	}
	
	static public function format(\Throwable $e): string{
		return self::trace_format($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
	}
}