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
 * @name Filesystem
 * @version 0.2.0
 * 
 */
class Filesystem extends Core
{
	public function __construct()
	{
		/* Init Core */
		if ( defined( 'FW_PATH_FW_CONFIG' ) ) {
			$path_config = FW_PATH_FW_CONFIG;
		}
		parent::__construct(@$path_config);
	}
	
	/**
	 * 
	 * Reads directory contents into array
	 * @param (string) $dir Directory Path
	 * @return (array) Returns directory contents in array or False on error
	 * 
	 */
	public function dirRead( $dir )
	{
		/* Check if directory exists */
		/* Open directory handle */
		/* Build directory array */
		/*** Series of Checks ***/
		if ( file_exists( $dir ) ) {
			if ( !is_dir( $dir ) ) {
				$this->errorId = 'ERRx0116';
				$this->errorMsg = "Error: is not a directory: '$dir'";
				return false;
			}
		} else {
			$this->errorId = 'ERR0108';
			$this->errorMsg = "Error: Directory not found '$dir'";
			return false;
		}
		
		if ( @is_resource( $this->storage[ 'dirHandles' ][ $dir ][ 'handle' ] ) ) 
			$h = $this->storage[ 'dirHandles' ][ $dir ][ 'handle' ];
		elseif ( !$h = @opendir( $dir ) ) {
			$this->errorId = 'ERR0111';
			$this->errorMsg = "Error: Unable to open directory '$dir': Permission Denied";
		} else {
			/* Store handle in memory */
			$this->storage[ 'dirHandles' ][ $dir ][ 'handle' ] = $h;
		}
		
		while( false !== ($file = readdir( $h ) ) ) {
			if ( $file != '.' && $file != '..' ) {
				$array[] = $file;
			}
		}
		return $array;
	}
}