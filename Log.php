<?php

class Log {
	/*static public function debug_sql(string $message){
		self::write($message, 'debug_sql_log');
	}
	
	static public function login_error(string $client, string $login, string $pass){
		self::write("Client: $client; Login: $login; Pass: $pass; IP: ".$_SERVER['REMOTE_ADDR'], 'login', true);
	}
	
	static public function mail(string $message, bool $is_error=false){
		self::write($message, 'mail', $is_error, false, 1);
	}
	
	static public function gateway(string $message){
		self::write($message, 'gateway');
	}
	
	static public function bs(string $message){
		self::write($message, 'bs');
	}
	
	static public function bs_mandate(string $message){
		self::write($message, 'bs_mandate');
	}
	
	static public function gateway_error(string $message){
		self::write("ERROR: $message", 'gateway');
	}
	
	static public function gateway_subscription(string $message){
		self::write($message, 'gateway_subscription');
	}
	
	static public function write(string $message, string $name, bool $is_error=false, bool $write_env=false, int $log_limit_mb=0, bool $timestamp=true){
		$file = CWD.'/'.Ini::get('path/log').'/'.$name.'.'.($is_error ? 'err' : 'log');
		
		if($log_limit_mb && is_file($file) && $log_limit_mb < filesize($file) / 1024 / 1024){
			self::rewind($file);
		}
		
		if($write_env){
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
		}
		
		$message = str_replace("\n", ' ', str_replace("\r", '', $message));
		$message = preg_replace('/ +/', ' ', $message);
		
		if($timestamp){
			$message = date('Y-m-d H:i:s', time() + Env::server_time_offset()).' '.$message;
		}
		
		file_put_contents($file, "$message\r\n", FILE_APPEND);
	}
	
	static public function read_log(string $name, bool $is_error=false): Array{
		$file = Ini::get('path/log').'/'.$name.'.'.($is_error ? 'err' : 'log');
		
		return is_file($file) ? array_reverse(file($file)) : [];
	}
	
	static private function rewind(string $file){
		$handle = fopen($file, 'r+');
		$content = fread($handle, filesize($file));
		
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
	}*/
}