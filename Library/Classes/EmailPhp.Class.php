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
 * @name Email
 * @version 0.2.0
 * 
 */
Class Email extends Framework
{
	/**
	 * @access Public
	 * @method validateEmail
	 * Enter description here ...
	 * @param (string) $email E-Mail string to validate
	 * @return (bool) True if E-Mail is valid or false on error
	 * 
	 */
	public function validateEmail( $email )
	{
		/*if(preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" , $email))*/
		/*** Feature Added 04/28/2011 : grab LG_Valid_Domain_Extensions configuration value ***/
		$ext = $this->config[ 'LG_Valid_Domain_Extensions' ];
		$ext = preg_replace( '/(,\s?)/' , '|' , $ext );
		if ( preg_match( "`^([a-zA-Z0-9-_.]+)@([a-zA-Z0-9-_]+\.($ext))$`" , $email , $match ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * @access Public
	 * @method send
	 * Sends E-Mail via PHP
	 * @param (string) $from
	 * @param (string) $fromName
	 * @param (string) $to
	 * @param (string) $subject
	 * @param (string) $message
	 * @param (string) $mime
	 * @return (bool) Returns the status of PHP mail() function
	 * 
	 */
	public function send( $from , $fromName , $to , $subject , $message , $mime = false )
	{
		/* Bugfix 02/01/2011 1:50 AM : Attempting to get Hotmail to accect our mail 
		 * This might be a problem with carriage return/new line, Hotmail SMTP Implementation */
		if ( preg_match( '`(@hotmail.com)$`' , $to ) ) {
			$line_ending = "\r\n";
		} else {
			$line_ending = "\n";
		}
		
		/* Include the name of the sender */
		if ( !empty( $fromName ) ) {
            $fromName = trim( $fromName );
			$headers = "From: $fromName <$from>$line_ending";
		} else {
			$headers = "From: $from$line_ending";
		}
		$headers .= 'X-Mailer: PHP/' . phpversion() . $line_ending;
		$headers .= "X-Mailer: My mailer";
		$bool = mail( $to , $subject , $message , $headers );
		return $bool;
	}	
}