<?php

namespace Log;

class Log {
	public const ERR_FATAL 				= 'fatal';
	public const ERR_WARNING 			= 'warning';
	
	private const WWW_USER 				= 'www-data';
	
	private const ERR_FATAL_LIMIT_MB 	= 10;
	private const ERR_WARNING_LIMIT_MB 	= 10;
	
	private const CRLF 					= "\r\n";
	
	static private $path;
	
	static private $num_errors 			= [];
	
	static public function init(string $path){
		self::$path = $path;
	}
	
	static public function log(string $message, string $name, int $log_limit_mb=1){
		self::write($message, $name, false, false, $log_limit_mb);
	}
	
	static public function err(string $message, string $name, bool $write_env=true){
		switch($name){
			case self::ERR_FATAL:
				$log_limit_mb = self::ERR_FATAL_LIMIT_MB;
				break;
			
			case self::ERR_WARNING:
				$log_limit_mb = self::ERR_WARNING_LIMIT_MB;
				break;
			
			default:
				$log_limit_mb = 1;
		}
		
		if(isset(self::$num_errors[$name])){
			self::$num_errors[$name]++;
		}
		else{
			self::$num_errors[$name] = 1;
		}
		
		self::write($message, $name, true, $write_env, $log_limit_mb);
	}
	
	static public function get_num_errors(string $name=''): int{
		if($name){
			return self::$num_errors[$name] ?? 0;
		}
		else{
			return array_sum(self::$num_errors);
		}
	}
	
	static public function get_log_file(string $name, bool $is_error=false, bool $timestamp=false): string{
		return self::$path.'/'.$name.($timestamp ? '_'.\Time\Time::file_timestamp() : '').'.'.($is_error ? 'err' : 'log');
	}
	
	static private function write(string $message, string $name, bool $is_error=false, bool $write_env=false, int $log_limit_mb=0){
		if(!self::$path){
			throw new Error('Log base path is not set');
		}
		
		$file = self::get_log_file($name, $is_error);
		
		$is_file = is_file($file);
		
		if($write_env){
			if(!empty($_SERVER['REQUEST_URI'])){
				$message .= '; URI: '.$_SERVER['REQUEST_URI'];
			}
			
			if(!empty($_POST)){
				$message .= '; POST:'.self::flatten_vars($_POST);
			}
			
			if(!empty($_SESSION)){
				$message .= '; SESSION:'.self::flatten_vars($_SESSION);
			}
		}
		
		//	Strip newlines
		$message = preg_replace('/ +/', ' ', str_replace("\n", ' ', str_replace("\r", '', $message)));
		
		//	Open file before size check and avoid race condition
		$f = fopen($file, 'a');
		
		//	Do log rotation if log file exceeds limit
		if($log_limit_mb && $is_file){
			clearstatcache(false, $file);
			$filesize = filesize($file);
			
			if($log_limit_mb < $filesize / 1024 / 1024){
				$f = self::rewind($file, $filesize);
			}
		}
		
		if(!fwrite($f, \Time\Time::timestamp_ms().' '.$message.self::CRLF)){
			throw new Error("Could not write to logfile: $file");
		}
		
		fclose($f);
		
		if(!$is_file){
			chown($file, self::WWW_USER);
		}
	}
	
	static private function rewind(string $file, int $filesize){
		$f 		= fopen($file, 'r+');
		$data 	= fread($f, $filesize);
		
		$gz = gzopen(self::rotate($file).'.gz', 'w9');
		gzwrite($gz, $data);
		gzclose($gz);
		
		ftruncate($f, 0);
		rewind($f);
		
		return $f;
	}
	
	static private function rotate(string $file): string{
		$dir 	= $file.'.d';
		$count 	= $dir.'/_last';
		
		if(!is_dir($dir)){
			mkdir($dir);
			chown($dir, self::WWW_USER);
			
			touch($count);
			chown($count, self::WWW_USER);
			
			$last = 0;
		}
		else{
			$last = file_get_contents($count);
		}
		
		$last++;
		
		file_put_contents($count, $last);
		
		return $dir.'/'.basename($file).'.'.$last;
	}
	
	static private function flatten_vars(array $vars): string{
		$output = '';
		foreach($vars as $key => &$value){
			$output .= " $key=".(is_array($value) ? '[array]' : $value);
		}
		
		return $output;
	}
}

class Error extends \Error {}