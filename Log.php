<?php

namespace Log;

class Log {
	const WWW_USER 		= 'www-data';
	
	const ERR_FATAL 	= 'fatal';
	const ERR_WARNING 	= 'warning';
	
	const ERR_FATAL_LIMIT 		= 10;
	const ERR_WARNING_LIMIT 	= 10;
	
	const CRLF = "\r\n";
	
	static private $path;
	
	static public function init(string $path){
		self::$path = $path;
	}
	
	static public function log(string $message, string $name, int $log_limit_mb=0){
		self::write($message, $name, false, false, $log_limit_mb);
	}
	
	static public function err(string $message, string $name){
		switch($name){
			case self::ERR_FATAL:
				$log_limit_mb = self::ERR_FATAL_LIMIT;
				break;
			
			case self::ERR_WARNING:
				$log_limit_mb = self::ERR_WARNING_LIMIT;
				break;
			
			default:
				$log_limit_mb = 10;
		}
		
		self::write($message, $name, true, true, $log_limit_mb);
	}
	
	static public function get_log_file(string $name, bool $is_error=false, bool $timestamp=false): string{
		return self::$path.'/'.$name.($timestamp ? '_'.\Time\Time::file_timestamp() : '').'.'.($is_error ? 'err' : 'log');
	}
	
	static protected function write(string $message, string $name, bool $is_error=false, bool $write_env=false, int $log_limit_mb=0){
		$file = self::get_log_file($name, $is_error);
		
		$is_file = is_file($file);
		
		//	Strip newlines
		$message = preg_replace('/ +/', ' ', str_replace("\n", ' ', str_replace("\r", '', $message)));
		
		if($write_env){
			if(!empty($_SERVER['REQUEST_URI'])){
				$message .= '; URI: '.$_SERVER['REQUEST_URI'];
			}
			
			if(!empty($_GET)){
				$message .= '; GET:'.self::flatten_vars($_GET);
			}
			
			if(!empty($_POST)){
				$message .= '; POST:'.self::flatten_vars($_POST);
			}
			
			if(!empty($_SESSION)){
				$message .= '; SESSION:'.self::flatten_vars($_SESSION);
			}
		}
		
		//	Add timestamp
		$message = \Time\Time::timestamp().' '.$message.self::CRLF;
		
		if(file_put_contents($file, $message, FILE_APPEND) === false){
			throw new \Error('Could not write to logfile: '.$file);
		}
		
		if(!$is_file){
			chown($file, self::WWW_USER);
		}
		
		if($log_limit_mb && is_file($file)){
			$filesize = filesize($file);
			if($log_limit_mb < $filesize / 1024 / 1024){
				self::rewind($file, $filesize);
			}
		}
	}
	
	static private function rewind(string $file, int $filesize){
		$handle 	= fopen($file, 'r+');
		$content 	= fread($handle, $filesize);
		
		$last = 0;
		foreach(glob($file.'.*.gz') as $f){
			$base = substr($f, 0, strrpos($f, '.'));
			$last = max(substr($base, strrpos($base, '.')+1), $last);
		}
		$last++;
		
		$gz = gzopen($file.'.'.$last.'.gz', 'w9');
		gzwrite($gz, $content);
		gzclose($gz);
		
		ftruncate($handle, 0);
		fclose($handle);
	}
	
	static private function flatten_vars(array $vars): string{
		$output = '';
		foreach($vars as $key => $value){
			$output .= " $key=".(is_array($value) ? '[array]' : $value);
		}
		
		return $output;
	}
}