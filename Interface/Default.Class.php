<?php

/*** 

	Copyright (c) http://wiki.bombshellz.net/
	Author: Lutchy Horace
	Version: 0.0.1
	
	Redistribution and use in source or binary forms are permitted provided that the following conditions are met:
		
		* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
		* Neither the name of the BombShellz.net nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
		* Modification to this file or program is not permitted without the consent of the author.
		* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
***/

/**
 * @category Class
 * @name Core
 * @version 0.2.0
 * 
 */
class Core
{
	
	public $errorId;
	public $errorMsg;
	public $storage; /* Storage container for values */
	public $config; /* Class configuration loaded from file */
	public $debug; /* Debug level set by configuration */
	public $initMode; /* Initialization mode */
	
	public function __construct( $path_config = null )
	{
		/* Set and load configuration */
		if ( empty( $path_config ) || !is_file( $path_config ) ) {
			$path_config = FW_PATH_CONFIG . 'Default.php';
		}
		require $path_config;
		if ( !defined( 'FW_PATH_FW_CONFIG' ) )
			define( 'FW_PATH_FW_CONFIG' , $path_config );
		
		$this->config   = $_CFG;
		$this->debug    = $_CFG[ 'LG_Debug' ];
		$this->initMode = 'init';
		
		/* Set timezone if undefined */
		if ( ini_get( 'date.timezone' ) == null ) {
			date_default_timezone_set( $_CFG[ 'LG_Default_Timezone' ] );
		}
		define( 'DATE_SYSTEM'    , date( 'r' ) );
		define( 'DATE'           , date( 'D, M d Y h:i:s A O ') );

		/* Enable debugging (verbose PHP) */
		if ( $this->debug >= 1 ) {
			ini_set( 'track_errors' , 'on' );
			ini_set( 'display_errors' , 'on' );
			error_reporting( E_ALL );
		} else {
			ini_set( 'track_errors' , 'off' );
			ini_set( 'display_errors' , 'off' );
			error_reporting( 0 );
		}
		
		/* Set temp directory if possible */
		if ( !defined( 'TEMP_DIR' ) ) 
			$this->enableTempDirectory();
	}
	
	/**
	 * @access Public
	 * @method loadClass
	 * @return (string) Class Name
	 * Loads Class Name into memory
	 * 
	 */
	public function loadClass( $className )
	{	
		$classFile = FW_PATH_LIB . 'Classes' . DS . $className . '.Class.php'; 
		if ( is_file( $classFile ) ) {
			require_once $classFile;
			if ( !class_exists( $className ) ) {
				$this->errorId = 'ERR0802';
				$this->errorMsg = 'Class doesn\'t appear to have loaded';
				if ( $this->debug > 2 ) {
					$this->throwError();
				}
			}
		} elseif ( $this->debug > 1 ) {
			$this->errorId = 'ERR0101';
			$this->errorMsg = 'Error: Class file could not be found: ' . $classFile;
			$this->throwError();
		}
	}
	
	/**
	 * @access Public
	 * @method backtrace
	 * @return (string) backtrace
	 * Builds backtrace string
	 * 
	 */
	public function createBacktrace()
	{
		/*** Feature Added 04.28.2011 : Reverse back trace ***/
		$array_backtrace = array_reverse( debug_backtrace() , true );
		$str = null;
		foreach( $array_backtrace as $level => $backtrace ) {
			$str .= "#$level ";
			if ( !empty( $backtrace[ 'class' ] ) ) {
				$str .= $backtrace[ 'class' ]. '->';
			}
			$str .= $backtrace[ 'function' ] . '(';
			if ( !empty( $backtrace[ 'args' ] ) ) {
				$func_args = null;
				foreach( $backtrace[ 'args' ] as $arg ) {
					if ( empty( $func_args ) ) {
						$func_args = $arg;
					} else {
						$func_args .= ", $arg";
					}
				}
				$str .= $func_args;
			}
			@$str .= ') called at [' . $backtrace[ 'file' ] . ':' . $backtrace[ 'line' ]. ']' . "\r\n";
			/*** BUG Fixed 04.28.2011 : $backtrace[object] can be empty. Don't assum it's always an object ***/
			if ( !empty( $backtrace[ 'object' ] ) ) {
				$str .= "#Object[$level]: \r\n";
				foreach( $backtrace[ 'object' ] as $properties => $value ) {
					$str .= " $properties => $value \r\n";
				}
				$str .= "\r\n";
			}
		}
		return $str;
	}
	
	/**
	 * @access Public
	 * @method strReplace
	 * @param (array) Array pair of key to search and value to replace
	 * @param (string) String to parse
	 * @param (string) File path to use to parse
	 * @return (string) Return a string with replace text
	 * String with replaced text 
	 */
	public function stripReplace( $replaceArr , $replaceStr , $file = false )
	{
		/* Open file for substition */
		if ( !empty( $file ) ) {
			if ( $this->initMode == 'init' || $this->initMode == 'error' ) {
				if ( !$replaceStr = @file_get_contents( $file ) ) {
					return false;
				}
			} elseif ( !$replaceStr = $this->fileRead( $file ) ) {
				return false;
			}
		}
		
		/* Build search and replace array */
		foreach( $replaceArr as $key => $value ) {
			$search[] = $key;
			$replace[] = $value;
		}
		
		return str_replace( $search , $replace , $replaceStr );
	}
	
	/**
	 * @access Public
	 * @method fileRead
	 * @param (string) Filename
	 * @return (string) File contents
	 * Reads file contents, if enabled, stores a copy in cache
	 *  
	 */
	public function fileRead( $file , $forceOpen = false )
	{
		/** series of checks **/
		if ( empty( $file ) ) return false;
		
		/*** Feature added Unknown : To speed access to the file, lets the file contents into memory.
		     This will increase memory consumtion.
		***/
		if ( $this->config[ 'LG_OW_Store_In_Memory' ] && $forceOpen == false ) {
			if ( !empty( $this->storage[ 'file_handler' ][ $file ] ) ) {
				return $this->storage[ 'file_handler' ][ 'contents' ];
			}
		}
		
		$handle = @$this->storage[ 'file_handler' ][ $file ][ 'handle' ];
		$read   = true;
		if ( is_resource( $handle ) ) {
			//var_dump( stream_get_meta_data( $handle ) );
			/* rewind handle */
			rewind( $handle ); 
			if ( !$data = fread( $handle ,  filesize( $file ) ) ) {
				$read = false;
			}
		} elseif ( is_file( $file ) ) {
			if ( $h = @fopen( $file , 'r+' ) ) {
				/*** Feature added 04/25/2011 : it seems there's problems
				 on subsequent reads in the same excution, this a workaround, let's keep the file handle
				 in memory during the excution.
		 		*/
				/** Store file handler in memory for later usage **/
				$this->storage[ 'file_handler' ][ $file ][ 'handle' ] = $h;
				if ( !$data = fread( $h ,  filesize( $file ) ) ) {
					$read = false;
				}
			} else {
				$this->errorId = 'ERR0105';
				$this->errorMsg = "Unable to open $file. PHP Error: " . $this->capturePhpError();
				if ( $this->debug >= 1 ) {
					$this->throwError();
				}
				return false;
			}
		}
		
		if ( $read ) {
			/** Store file contents in memory **/
			if ( $this->config[ 'LG_OW_Store_In_Memory' ] ) {
				$this->storage[ 'file_handler' ][ $file ][ 'contents' ] = $contents;
			}
			return @$data;
		} else {
			$this->errorId = 'ERR0104';
			$this->errorMsg = "Unable to read $file. PHP Error: {$this->capturePhpError()}";
			if ( $this->debug > 1 ) {
				$this->throwError();
			}
		}
		
		return false;	
	}
	
	/**
	 * @access Public
	 * @method fileWrite
	 * @param (string) String to write
	 * @param (string) file path, if left empty, a temp file will be created, and temp name returned reference variable $filename
	 * @param (array) Options: 
	 *   wmode (string) Write mode supported by fopen(); IE. w or w+
	 *   pmode (int) Permission mode in octate
	 *   backup (bool) Store a backup of the file prior to writing
	 *   IE. array( 'wmode' => 'w' , 'pmode' => 0700 , false );
	 * @param (&var) Reference to the written file path
	 * @return (bool)
	 * Writes data to specified file or temporary file.
	 * 
	 */
	public function fileWrite( $data , $file = null , $options = array() , &$filename = false )
	{
		/* Set default options */
		if ( !empty( $options ) && is_array( $options ) ) {
			$writeMode   = @$options[ 'wmode' ];
			$writePerm   = @$options[ 'pmode' ];
			$writeBackup = @$options[ 'backup' ];
		}
		
		$is_temp_file = false;
		/* Error checking and fixing */
		if ( $file == null || empty( $file ) ) {
			/* Create a temp file */
			$file = tempnam( $this->tempDir , 'lg_' );
			$is_temp_file = true;
		} elseif ( !empty( $writeBackup ) ) {
			/* Feature added: 01/15/2011 */
			/* Only make a back of the file, if file name is provided 
			 * No sense to make a backup of a temp file 
			 * We might not have the permission to write to directory, check if directory is writeasble
			 * */
			$directory = dirname( $file ) . DS;
			if ( !is_writable( $directory ) ) {
				/* Raise error */
				$this->errorId  = 'ERR07107';
				$this->errorMsg = "Directory ($directory) is not writeable, unable to make backup";
				
				if ( $this->debug > 1 ) {
					$this->throwError();
				} 
			} elseif ( is_file( $file ) ) {
				$dest = $directory . basename( $file ) . '.bak.0';
				/* Feature added: 01/18/2011 */
				/* Make incremental backups **/
				/* Revision: 01/18/2011 8:16 PM
				 * Make backup a suffix instead of prefix */
				$i = 1;
				$count = 1;
				$first_file = null;
				while( is_file( $dest ) ) {
					if ( empty( $first_file ) ) {
						$first_file = $dest;
					}
					$dest = $directory . basename( $file ) . '.bak.' . $i;
					$i++;
					$count++;
				}
				
				/* Remove the first file if the count is greater then what's configured */
				if ( $count > $this->config[ 'LG_OW_Max_Backup' ] ) {
					unlink( $first_file );
				}
				copy( $file , $dest );
			}
		}
		
		/*** Series of Checks ***/
		if ( empty( $writeMode ) ) {
			$writeMode = 'w';
		}
		if ( empty( $writePerm ) ) {
			$writePerm = 0700;
		}
		
		/* Retrieve handle */
		$handle = @$this->storage[ 'fila_handler' ][ $file ][ 'handle' ];
		if ( is_resource( $handle ) ) {
			$h = $handle;
		} elseif ( !$h = @fopen( $file , $writeMode ) ) {
			$this->errorId = 'ERR0105';
			$this->errorMsg = "Unable to open $file for writing. PHP Error: " . $this->capturePhpError();
			$this->throwError();
		} else {
			/* Store handle */
			$this->storage[ 'fila_handler' ][ $file ][ 'handle' ] = $h;	
		}
		
		/* Feature added: 01/18/2011 */
		/*** Feature modified 03/02/2011 3:29 PM : See BUG FIX below, pass back reference if data is empty
		 * fwrite will return false, this is a workaround
		 * ***/
		/* Pass back the reference */
		if ( empty( $data ) ) {
			$filename = $file;
		}
		
		if ( @fwrite( $h , $data ) ) {
			/* Feature added: 01/18/2011 */
			/*** Feature modified 03/02/2011 3:29 PM : See BUG FIX below ***/
			/* Pass back the file name we wrote to */
			$filename = $file;
				
			/*** Feature added 02/12/2011 9:25 PM : Store file contents in memory ***/
			if ( $this->config[ 'LG_OW_Store_In_Memory' ] ) {
				if ( !$is_temp_file ) {
					/* if it's in Append mode, then it's not the entire file contents */
					if ( $writeMode != 'a' && $writeMode != 'a+' ) {
						$this->storage[ 'file_handler' ][ $file ][ 'contents' ] = $data;
					} else {
						/* ReRead the entire file contents after append write */
						$this->fileRead( $file , true );
					}
				}
			}
			/*** Done ***/
				
			/*** Feature repaired 03/01/2011 3:09 PM : I didn't add the feature to set perms o_0 ***/
			chmod( $file , $writePerm );
			return true;
			/*** Feature modifed (BUG FIX) 03/01/2011 2:52 PM : Just because it didn't write with no data,
			 * Didn't mean it occurred an error. FIXED
			 */
		} elseif ( !empty( $data ) ) {
			$this->errorId = 'ERR0106';
			$this->errorMsg = "Unable to write to $file. PHP Error: " . $this->capturePhpError();
			$this->throwError();
		}
		return false;
	}
	
	public function makeDir( $dir , $mode = 0775 )
	{
		if ( empty( $dir ) ) {
			$this->errorId = 'ERRx0109';
			$this->errorMsg = 'Missing directory to create';
			$this->throwError();
			return false;
		}
		if ( !is_int( $mode ) ) {
			$mode = 0775;
		}
		if ( !@mkdir( $dir , $mode , true ) ) {
			$this->errorId = 'ERRx0109';
			$this->errorMsg = "Unable to create $dir. PHP Error: {$this->capturePhpError()}";
			$this->throwError();
			return false;
		}
		return true;
	}
	
	/**
	 * @access Private
	 * @method enableTempDirectory
	 * @return (null)
	 * Set's temp directory if possible
	 * 
	 */
	private function enableTempDirectory()
	{
		$temp_dir = path_rewrite( $this->config[ 'LG_Temp' ] );
		
		if ( !empty( $temp_dir ) ) {
			if ( is_dir( $temp_dir ) ) {
				/* Check if temp directory is writeable, is_writeable seem to be unrealiable
		   		   on windows, write to the directory instead */
				/* Feature modified: 01/20/2011 8:28 PM : We don't need to check temp is writeable on every request
				 * let's limit this to debug level 1 */
				if ( $this->debug >= 1 ) {
					$temp_is_writeable = false;
					if ( FW_OS == 'Linux' ) {
						if ( is_writeable( $temp_dir ) )
							$temp_is_writeable = true;
					} elseif ( @file_put_contents( $temp_dir . 'writeable.txt' , 'Write test'  ) ) {
						$temp_is_writeable = true;
					}
			
					if ( !$temp_is_writeable ) {
						$this->printError( 'ERR0107' , 'Temp directory is not writeable' );
						exit;
					}
				}
				
				if ( !preg_match( '`(/|\\\)$`' , $temp_dir ) ) {
					$temp_dir .= DS;
				}
				define( 'TEMP_DIR' , $temp_dir );
			} elseif ( $this->debug >= 1 ) {
				$this->errorId = 'ERR0108';
				$this->errorMsg = 'Error: Temp directory is not found';
				$this->throwError();	
			}
		}
	}
	
	/**
	 * @access Public
	 * @method logData
	 * @param (string) Error ID
	 * @param (string) Error Message
	 * @param (array) Error Location
	 *   IE: array( 'line' => __LINE__ , 'file' => __FILE__ );
	 * @param (string) Error Type
	 * @return (string) Returns PHP error if available
	 * Logs data to log file
	 * 
	 */
	public function logData( $errCode , $errContent , $errLocation= array() , $errType = 'USR' ) 
	{
		/*** Global Vars ***/
  		
  		/* Loggin is disabled */
  		if ( $this->config[ 'LG_Log_Error' ] != true ) {
  			return false;
  		}
  		
  		/* Set log path */
  		if ( empty( $this->config[ 'LG_Log_Path' ] ) ) {
  			$log_file = $this->getTempPath() . 'log' .DS;
  		} else {
  			$log_file = $this->config[ 'LG_Log_Path' ];
  		}
  		if ( !is_dir( $log_file ) ) {
  			if ( !$this->makeDir( $log_file ) ) {
  				$this->errorId = 'ERR0202';
				$this->errorMsg - 'Log error, unable to write to log. Suggest turning debug on.';
  			}
  		}
   		$log_file .= $this->config[ 'LG_Log_Name' ];
   		
   		
   		/* Error checking and fixing */
		if ( empty( $errCode ) ) {
			$errCode = 'ERR0000';
		}
   		if ( !preg_match( "`(SYS|SEC|USR|WAR)`" , $errType ) ) {
   			$errType = 'USR';	
   		}
   		if ( empty( $errContent ) ) {
   			$errContent = "Unknown";
   		}   
   		if ( !empty( $errLocation ) && is_array( $errLocation ) ) {
   			if ( ( empty( $errLocation[ 'line' ] ) ) || ( preg_match( '/[A-Z]+/i',  $errLocation[ 'line' ] ) ) ) {
   				$errLine = 'N/A';	
   			} else {
   				$errLine = $errLocation[ 'line' ];
   			}
   			if ( empty( $errLocation[ 'file' ] ) ) {
   				$errFile = 'N/A';
   			} else {
   				$errFile = $errLocation[ 'file' ];
   			}
   		} else {
   			$errLine = 'N/A';
   			$errFile = 'N/A';
   		}
   		
   		$log_contents = 'Date=' . DATE_SYSTEM . ", Type=$errType, Id=$errCode, Details=$errContent, Line=$errLine, File=$errFile\n";
		if ( !$this->fileWrite( $log_contents , $log_file , array( 'wmode' => 'a' ) ) ) {
			$this->errorId = 'ERR0202';
			$this->errorMsg - 'Log error, unable to write to log. Suggest turning debug on.';
			return false;
		}	
		return true;
	}
	
	/**
	 * @access Public
	 * @method capturePhpError
	 * @return (string) Returns PHP error if available
	 * Print a date format text string 
	 * 
	 */
	public function capturePhpError()
	{
		if ( empty( $php_errormsg ) ) {
			return 'Unknown';
		}
		return $php_errormsg;
	}
	
	/**
	 * @access Public
	 * @method createRandomStr
	 * @param (int) String length
	 * @return (string) Returns a string a random characters
	 * Creates a string of random characters specified by length.
	 * 
	 */
	public function createRandomStr( $len = 8 )
	{
		$randstr = '';
    	srand((double)microtime()*1000000);
    	for($i=0;$i<$len;$i++){
        	$n = rand(48,120);
        	while (($n >= 58 && $n <= 64) || ($n >= 91 && $n <= 96)){
            	$n = rand(48,120);
        	}
        	$randstr .= chr($n);
    	}
    	return $randstr;
	}
	
	/**
	 * @access Private
	 * @method throwError
	 * @return (null)
	 * A simple wrapper around printError that utililizes
	 * errorId and errorMsg
	 * 
	 */
	protected function throwError()
	{
		$this->printError( $this->errorId , $this->errorMsg );
		exit(1);
	}
	
	/**
	 * @access Private
	 * @method getSapi
	 * @return (string) Server Sapi
	 * Retrieves current save sapi
	 *  
	 */
	public function getSapi()
	{
		return $this->sapi;
	}
	
	/**
	 * @access Public
	 * @method getTempDir
	 * @return (string) Return temporary directory path
	 * Retrieves the current temporary Directory
	 * 
	 * Possible bug fix to prevent $this->tempDir from
	 * being modiffied. 
	 * 
	 */
	public function getTempPath()
	{
		return TEMP_DIR;
	}
	
	
	
}