<?php

namespace Log;

class Err extends Log {
	static public function fatal(\Throwable $e): string{
		$error = self::format($e);
		if($prev_e = $e->getPrevious()){
			$error .= "\nPrevious error: ".self::format($prev_e);
		}
		
		self::err($error, self::ERR_FATAL, 1);
		
		return $error;
	}
	
	static public function format(\Throwable $e): string{
		return $e->getMessage().' '.$e->getFile().'('.$e->getLine().")\n".$e->getTraceAsString();
	}
}

class Fatal extends \Error {
	public function __construct(string $message, int $code=0, Throwable $previous=null){
		parent::__construct($message, $code, $previous);
		Err::fatal($this);
	}
}