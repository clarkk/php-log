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
	
	static public function format(\Error $e): string{
		return $e->getMessage().' '.$e->getFile().'('.$e->getLine().")\n".$e->getTraceAsString();
	}
}

class Error extends \Error {}

class Fatal extends Error {
	public function __construct(string $message, int $code=0, Throwable $previous=null){
		parent::__construct($message, $code, $previous);
		Err::fatal($this);
	}
}