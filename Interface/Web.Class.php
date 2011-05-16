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

define( 'DEFAULT_ERROR_BLOCK' , FW_PATH_LIB . 'errorBlockWeb.txt' );

/** Set server software **/
if ( !preg_match( '`((Microsoft-IIS)/(.*)\s?)`' , $_SERVER[ 'SERVER_SOFTWARE' ] , $match ) ) {
	if ( !preg_match( '`((Apache)/((\d|\.)*))`' , $_SERVER[ 'SERVER_SOFTWARE' ] , $match ) ) {
		/** Next Rule to check **/
	}
}
define( 'FW_SERVER_APP_NAME' , $match[2] );
define( 'FW_SERVER_APP_VER'  , $match[3] );

/**
 * @category Class
 * @name Framework
 * @version 0.2.0
 * 
 */
class Framework extends Core
{
	protected $sapi = 'web';
	
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
	public function printError($errorid, $errormsg)
	{
		/* Set default hidden message */
		$hidden_msg = 'Unfortunately, we\'ve encountered an error at this time';
		$safe_ip = explode( ',' , $this->config[ 'LG_Safe_Client_IP' ] );
		if ( !in_array( $_SERVER[ 'REMOTE_ADDR' ] , $safe_ip ) ) {
			$errormsg = $hidden_msg;
			$backtrace = null;
		}
		
		$replace[ '<%version%>' ]      = FW_VERSION;
		$replace[ '<%message_type%>' ] = '<span style="color: red;">Error</span>';
		$replace[ '<%message_code%>' ] = $errorid;
		$replace[ '<%config_name%>' ]  = $this->config[ 'LG_Config_Name' ];
		if ( $this->debug == 2 ) {
			$textarea = '<br /><strong>Backtrace</strong><br />' . "\r\n";
			$textarea .= '<textarea rows=20 cols=100>' . "\r\n";
			$textarea .= $this->createBacktrace();
			$textarea .= '</textarea>';
			$replace[ '<%backtrace%>' ] = $textarea;
		} else {
			$replace[ '<%backtrace%>' ] = null;
		}
		$replace[ '<%message%>' ] = $errormsg;
		$this->initMode = 'error';
		if ( !$str = $this->stripReplace( $replace , null , DEFAULT_ERROR_BLOCK ) ) {
			/** Remainder, if you make adjustments here, remember to make adjustments in Cli.Class.php **/
			$str = "Error ID: $errorid\n";
			$str .= "Error Message: $errormsg\n\n";
			$str .= "Error ID: ERRx0118\n";
			$str .= "Error Message: Unable to retrieve Error Template\n";
		}
		print $str;
	}
	
}