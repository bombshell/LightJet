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
		if ( empty( $path_config ) ) {
			$path_config = FW_PATH_CONFIG . 'Default.php';
		}
		require $path_config;
		$this->config   = $_CFG;
		$this->debug    = $_CFG[ 'LG_Debug' ];
		$this->initMode = 'init';
		
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
		$this->enableTempDirectory();
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
		$array_backtrace = debug_backtrace();
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
			$str .= ') called at [' . $backtrace[ 'file' ] . ':' . $backtrace[ 'line' ]. ']' . "\r\n";
			$str .= "#Object[$level]: \r\n";
			foreach( $backtrace[ 'object' ] as $properties => $value ) {
				$str .= " $properties => $value \r\n";
			}
			$str .= "\r\n";
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
			if ( $this->initMode == 'init' ) {
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
		
		/*** Feature modified 02/12/2011 9:13 PM : it seems there's problems
		on subsequent reads in the same excution, this a workaround, let's us
		store what we read in memory. Also, let's add an option to store in memory
		or not. Note, this doesn't take into account changes to the file made by other
		applications.
		 */
		if ( $this->config[ 'LG_OW_Store_In_Memory' ] && $forceOpen == false ) {
			if ( !empty( $this->storage[ 'file_handler' ][ $file ] ) ) {
				return $this->storage[ 'file_handler' ][ 'contents' ];
			}
		}
		
		$handle = $this->storage[ 'fila_handler' ][ $file ][ 'handle' ];
		$read   = true;
		if ( is_resource( $handle ) ) {
			if ( !$data = @fread( $handle ,  filesize( $file ) ) ) {
				$read = false;
			}
		} elseif ( is_file( $file ) ) {
			if ( $h = @fopen( $file , 'r+' ) ) {
				/** Store file handler in memory for later usage **/
				$this->storage[ 'fila_handler' ][ $file ][ 'handle' ] = $h;
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
					if ( FRAMEWORK_OS == 'Linux' ) {
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
	 * @method capturePhpError
	 * @return (string) Returns PHP error if available
	 * Print a date format text string 
	 */
	public function capturePhpError()
	{
		if ( empty( $php_errormsg ) ) {
			return 'Unknown';
		}
		return $php_errormsg;
	}
	
	/**
	 * @access Private
	 * @method throwError
	 * @return (null)
	 * A simple wrapper around printError that utililizes
	 * errorId and errorMsg
	 * 
	 */
	private function throwError()
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
	function getSapi()
	{
		return $this->sapi;
	}
	
	
}