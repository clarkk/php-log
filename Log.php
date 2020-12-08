<?php

namespace Log;

require_once 'Err.php';

class Log {
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
	
	static private function write(string $message, string $name, bool $is_error=false, bool $write_env=false, int $log_limit_mb=0){
		$file = self::$path.'/'.$name.'.'.($is_error ? 'err' : 'log');
		
		//	Strip newlines
		$message = preg_replace('/ +/', ' ', str_replace("\n", ' ', str_replace("\r", '', $message)));
		
		//	Add timestamp
		$message = date('Y-m-d H:i:s', time() + (new \DateTimeZone('Europe/Copenhagen'))->getOffset(new \DateTime('now'))).' '.$message.self::CRLF;
		
		if(file_put_contents($file, $message, FILE_APPEND) === false){
			throw new \Error('Could not write to logfile: '.$file);
		}
		
		if($log_limit_mb && is_file($file)){
			$filesize = filesize($file);
			if($log_limit_mb < $filesize / 1024 / 1024){
				self::rewind($file, $filesize);
			}
		}
		
		/*if($write_env){
			$message .= '; URL: '.URLPATH;
			
			if(!empty($_GET)){
				$message .= '; GET:';
				foreach($_GET as $key => $value){
					$message .= " $key=".(is_array($value) ? '[array]' : $value);
				}
			}
			
			if(!empty($_POST)){
				$message .= '; POST:';
				foreach($_POST as $key => $value){
					$message .= " $key=".(is_array($value) ? '[array]' : $value);
				}
			}
			
			if(!empty($_SESSION)){
				$message .= '; SESSION:';
				foreach($_SESSION as $key => $value){
					$message .= " $key=".(is_array($value) ? '[array]' : $value);
				}
			}
		}*/
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
}