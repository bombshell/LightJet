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
 * @name Http
 * @version 0.2.0
 * 
 */
class Http extends Framework
{
	public $errorId;
	public $errorMsg;
	
	public $cfg; /* Array container for HTTP configuration */
	
	/**
	 * @method __construct
	 * Init the class configuration
	 * @param (string) Path to Http configuration file
	 * @return (null)
	 * 
	 */
	public function __construct( $config_path = null )
	{
		/** init Core **/
		parent::__construct( $config_path );
		
		/* Check Sapi */
		if ( $this->sapi != 'web' && $this->debug >= 1 ) {
			$this->errorId = 'ERR0801';
			$this->errorMsg = "Error: Invalid SAPI '{$this->sapi}': Only web is supported";
			$this->throwError();
		}
		
		/* Load our config */
		if ( empty( $config_path ) ) {
			$config_path = FW_PATH_CONFIG . 'Http.Config.php';
		}
		if ( !is_file( $config_path ) && $this->debug >= 1 ) {
			$this->errorId = 'ERR0501';
			$this->errorMsg = "Http config path is invalid: $config_path";
			$this->throwError();
			exit;
		}
		require $config_path;	
		$this->cfg = $httpCfg;
	}
	
	/**
	 * @method isHostvalid
	 * Checks if the URL is valid
	 * @param (string) $host host to check
	 * @return (bool) If the host is valid, or false on failure
	 * 
	 */
	public function isHostvalid( $host )
	{
		return filter_var( $host , FILTER_VALIDATE_URL );
	}
	
	/**
	 * @method redirectClient
	 * Redirect client using a mixture of HTTP Headers or Javascript
	 * Note: This method will only return true if the headers or javascript code was sent to client
	 * 		 and not if the client was actually have been redirected.
	 * Note: This method does not check if the target location is valid
	 * @param (string) $url URL to redirect client to
	 * @return (bool) True if it was sent to the browser or false on error
	 * 
	 */
	public function redirectClient( $url ) 
	{	
		if ( !empty( $url ) ) {
			if ( !preg_match( '`(http(s?)://)(.*?)/`' , $url ) ) {
				$url = preg_replace( '`^/`' , '' , $url );
				$url = $this->getHost(null) . $url;
			}
			/* check if url is valid */
			if ( $this->isHostvalid( $url ) ) {
				$parts = parse_url( $url );
				if ( strlen( @$parts[ 'query' ] ) < 2000 ) {
					if ( headers_sent() ) {
						print "\r\n<script type=\"text/javascript\">\r\n<!--\r\n" .
						      "window.location = \"$url\"\r\n" .
						      "//-->\r\n</script>";	
					} else {
						header( 'Location: ' . $url );
					}
					return true;
				} elseif ( $this->debug >= 1 ) {
					$this->errorId = 'ERR0304';
					$this->errorMsg = 'Error: Url Protection: Query is too long, over 2000 chars';
					$this->logData( $this->errorId , $this->errorMsg , null , 'SYS' );
					$this->throwError();
					exit;
				}
			}
			
		}
		return false;
	}
}