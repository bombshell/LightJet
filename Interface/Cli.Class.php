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

define( 'DEFAULT_ERROR_BLOCK' , FW_PATH_LIB . 'errorBlockCli.txt' );

if ( FW_OS == 'Linux' ) {
	define( 'FW_SERVER_APP_NAME' , 'Linux' );
	define( 'FW_SERVER_APP_VER'  , `uname -r` );
} else {
	define( 'FW_SERVER_APP_NAME' , 'Uknown' );
	define( 'FW_SERVER_APP_VER'  , 'Unknown' );
}
		
class Framework extends Core
{
	public $sapi = 'cli';
	
	public function __construct( $config_path = null )
	{
		/** init Core **/
		parent::__construct( $config_path );
		$this->initMode = 'running';
	}
	
	/**
	 * @access Public
	 * @method printError
	 * @param (string) Error ID code
	 * @param (string) Error Message associated with the Error ID
	 * @return (null)
	 * Pretty Print's the error
	 * Note: This function in a web environment will verify remote IP address
	 * Note: This function, if debug level 2, will include a backtrace in the output
	 *  
	 */
	public function printError( $errorid , $errormsg )
	{
		$replace[ '<%version%>' ]      = FW_VERSION;
		$replace[ '<%message_type%>' ] = 'Error';
		$replace[ '<%message_code%>' ] = $errorid;
		$replace[ '<%config_name%>' ]  = $this->config[ 'LG_Config_Name' ];
		
		if ( $this->debug == 2 ) {
			$replace[ '<%backtrace%>' ] = $this->createBacktrace();
		} else {
			$replace[ '<%backtrace%>' ] = null;
		}
		$replace[ '<%message%>' ] = $errormsg;
		$this->initMode = 'error';
		if ( !$str = $this->stripReplace( $replace , null , DEFAULT_ERROR_BLOCK ) ) {
			/** Remainder, if you make adjustments here, remember to make adjustments in Web.Class.php **/
			$str = "Error ID: $errorid\n";
			$str .= "Error Message: $errormsg\n\n";
			$str .= "Error ID: ERRx0118\n";
			$str .= "Error Message: Unable to retrieve Error Template\n";
		}
		$handle = fopen( 'php://stderr' , 'w' );
		fwrite( $handle , $str );
		fclose( $handle );
		exit(1);
	}
}