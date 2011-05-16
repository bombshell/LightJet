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
 * @name Session
 * @version 0.2.0
 * 
 */
class Session extends Framework
{
	public $errorId;
	public $errorMsg;
	
	public function __construct()
	{
		/** init Core **/
		parent::__construct( $config_path );
	}
	
	/**
	 * @access Public
	 * @method __construct
	 * Initiates a new session or restores a previously created session
	 * @param (string) Configuration file path
	 * @return (bool) True if session was created successfully
	 * 
	 */
	public function __construct( $config_path = null )
	{
		/* Set out config path */
		if ( empty( $config_path ) ) {
			$config_path = FW_PATH_CONFIG . 'Session.Config.php';
		}
		
		/* Series of checks */
		if ( $this->debug >= 1 ) {
			/* Check Sapi */
			if ( $this->sapi != 'web' ) {
				$this->errorId = 'ERR0801';
				$this->errorMsg = "Error: Invalid SAPI '{$this->sapi}': Only web is supported";
			}
			/* Check if the config file exists */
			if ( !is_file( $config_path ) ) {
				$this->errorId = 'ERRO501';
				$this->errorMsg = "Error: Session config path is invalid: $config_path";
			}
			$this->throwError();
		}
		
		/* Check if the temp directory is enabled */
		if ( empty( $this->getTempPath() ) ) {
			$this->errorId = 'ERR0501';
			$this->errorMsg = 'Unable to create session, temp directory is not enabled';
			return false;
		}
		require $config_path;
		
		/* set session type */
		switch( $sessCfg[ 'session_type' ] ) 
		{
			case 'php':
				/* Change the location of where we save our sessions */
				session_save_path( $this->getTempPath() );
			break;
		}
		
		session_name( $LightJet->config[ 'LG_Config_Name' ] );
		
		/* Start session */
		If ( !session_start() ) {
			$this->errorId = 'ERR0501';
			$this->errorMsg = 'Unable to create session for unknown reason. PHP Error: ' . $LightJet->capturePhpError();
			if ( $this->debug >= 1 ) {
				$this->throwError();
				exit;
			}
			return false;
		}
		
		/* Feature needs to be added to check if session hasn't expired, since session_start can resume an old session */
		
		$_SESSION[ 'session_status' ] = 'active';
		/* Feature added: 011/21/2011 12:45 AM : Make sure this session sticks to the client who created it.
		 * Prevent session high jacking */
		$_SESSION[ 'remote_addr' ] = $_SERVER[ 'REMOTE_ADDR' ];
		
	}
	
	/**
	 * @access Public
	 * @method isValid
	 * Checks if session is valid
	 * @return (bool) True if session is valid, False if is not
	 * 
	 */
	public function isValid()
	{
		if ( isset( $_SESSION ) && @$_SESSION[ 'session_status' ] == 'active' && @$_SESSION[ 'remote_addr' ] == $_SERVER[ 'REMOTE_ADDR' ] ) {
			return true;
		}
		return false;
	}
	
	/**
	 * @access Public
	 * @method destroy
	 * Destroys current session
	 * 
	 */
	public function destroy()
	{
		return session_destroy();
	}
}