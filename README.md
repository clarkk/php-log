# php-log
Logging with simple log rotation triggered by a file size limit in MB.

## Catch error (with trace back)
```
//  Set the base path to store log files
\Log\Log::init('log');

try{
	throw new Error('Panic!');
}
catch(Error $e){
	\Log\Err::fatal($e);
}
```

```
log/fatal.err:
2022-04-09 02:08:22.671 Panic! /var/www/main.php(14) #0 /var/www/main.php(14) #1 {main}
```

## Log
```
//  Set the base path to store log files
\Log\Log::init('log');

//  Log something with log rotation disabled
\Log\Log::log('Something is logged with rotation disabled', 'name-of-log', 0);

//  Log something with log rotation (default limit 1 MB)
\Log\Log::log('Something is logged with default log rotation', 'name-of-log');

//  Log something with log rotation limit 3 MB
\Log\Log::log('Something is logged with custom log rotation 3MB', 'name-of-log', 3);
```

```
log/name-of-log.log:
2022-04-09 02:08:19.926 Something is logged with rotation disabled
2022-04-09 02:08:19.954 Something is logged with default log rotation
2022-04-09 02:08:20.109 Something is logged with custom log rotation 3MB
```

## Error log
```
//  Set the base path to store the files
\Log\Log::init('log');

//  Log an error (By default the error message is extended by environment variables: $_SERVER['REQUEST_URI'], $_POST, $_SESSION)
\Log\Log::err('A warning is logged', \Log\Log::ERR_WARNING);

//  Log an error without environment variables
\Log\Log::err('A fatal error happened!', \Log\Log::ERR_FATAL, false);
```

```
log/warning.err:
2022-04-09 02:08:21.514 A warning is logged; URI: /path?query; POST: foo=bar; SESSION: id=123 user=test age=25
```

```
log/fatal.err:
2022-04-09 02:08:21.664 A fatal error happened!
```

## Log rotation
By default log rotation is set to 1 MB on logging with `\Log\Log::log('log message', 'name-of-log')`, and the archived log files is encoded with `gz`

### File structure
```
log/name-of-log.log
log/name-of-log.d/name-of-log.1.gz
log/name-of-log.d/name-of-log.2.gz
log/name-of-log.d/name-of-log.3.gz
```