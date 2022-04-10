# php-log
Logging with simple log rotation triggered by a file size limit.
- Log rotation (Default 1 MB for `.log` files and 10 MB for `.err` files)
- All fatal errors and core/compiler/parse errors are automatically logged (even if PHP can't execute the script)
- All warnings, notice etc. are automatically logged

### php.ini (production)
```
error_reporting         = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors          = Off
display_startup_errors  = Off
```

### php.ini (dev/test)
```
error_reporting         = E_ALL
display_errors          = On
display_startup_errors  = On
```

## How to use
Wrap all your code inside a `try..catch` block and all uncatched errors will be catched and logged.

**Note**: It's important that `Log::init()` is initiated as the first thing in your scripts
```
<?php

try{
	require_once 'Log.php';
	
	//  Set the base path to store log files
	\Log\Log::init('log');
	
	//  PHP core/compiler errors and PHP warnings/notice will be visible for the user
	//  (Do not enable this in production!)
	\Log\Log::verbose();
	
	//  Log something
	\Log\Log::log('user-event', 'User event: A user did something and I want to log it');
	
	//  Log a warning
	//  (This will NOT be visible to the user even if verbose is enabled)
	\Log\Log::err(\Log\Log::ERR_WARNING, 'A warning is logged');
	
	//  Log a fatal error
	//  (This will NOT be visible to the user even if verbose is enabled)
	\Log\Log::err(\Log\Log::ERR_FATAL, 'This might cause some damage');
	
	try{
		throw new Error('Something went terrible wrong');
		
		echo "Everything went OK!"
	}
	catch(Error $e){
		//  The fatal error is logged with a stack trace
		//  (This will NOT be visible to the user even if verbose is enabled)
		\Log\Err::fatal($e);
	}
}
//  Catch all uncatched and unexpected errors
catch(Throwable $e){
	//  Always try to return a generic HTTP error code with useful HTML/JSON response if possible
	if(isset($API)){
		\Log\Err::fatal($e);
		$API->error_response($e);
	}
	//  Fallback if nothing has yet been initialized
	else{
		\Log\Err::catch_all($e);
	}
}
```

## Log error (with stack trace)
Error message is extended by environment variables `$_SERVER['REQUEST_URI']`, `$_POST` and `$_SESSION`
```
try{
	throw new Error('Panic!');
}
catch(Error $e){
	\Log\Err::fatal($e);
}
```

```
log/fatal.err:
-------------------------
2022-04-09 02:08:22.671 Panic! /var/php/cronjob/test.php(7)
#0 /var/php/test/Test.php(18): test()
#1 {main}; URI: /path?query; POST: foo=bar; SESSION: id=123 user=test age=25
```

## Log error
Error message is extended by environment variables `$_SERVER['REQUEST_URI']`, `$_POST` and `$_SESSION`
```
\Log\Log::err(\Log\Log::ERR_WARNING, 'A warning is logged');
```

```
log/warning.err:
-------------------------
2022-04-09 02:08:21.514 A warning is logged; URI: /path?query; POST: foo=bar; SESSION: id=123 user=test age=25
```

## Log error (without environment variables)
```
\Log\Log::err(\Log\Log::ERR_FATAL, 'A fatal error happened!', false);
```

```
log/fatal.err:
-------------------------
2022-04-09 02:08:21.664 A fatal error happened!
```

## Log
```
//  Log something with log rotation disabled
\Log\Log::log('name-of-log', 'Something is logged with rotation disabled', 0);

//  Log something with log rotation (default limit 1 MB)
\Log\Log::log('name-of-log', 'Something is logged with default log rotation');

//  Log something with log rotation limit 3 MB
\Log\Log::log('name-of-log', 'Something is logged with custom log rotation 3MB', 3);
```

```
log/name-of-log.log:
-------------------------
2022-04-09 02:08:19.926 Something is logged with rotation disabled
2022-04-09 02:08:19.954 Something is logged with default log rotation
2022-04-09 02:08:20.109 Something is logged with custom log rotation 3MB
```

## Log rotation
By default log rotation is set to 1 MB on logging by `\Log\Log::log('name-of-log', 'log message')`, and the archived log files are encoded with `gz`

### File structure
```
log/name-of-log.log
log/name-of-log.d/name-of-log.1.gz
log/name-of-log.d/name-of-log.2.gz
log/name-of-log.d/name-of-log.3.gz
```