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

/** Framework Loader **/

/** Settings **/

/*
	Root path of this framework.
	
 	 Note: If running this framework on WIndows, remember to use the proper prefix. IE. X:\
 	 
	 Note: If left black, this file base directory will be considered as the
	       root path.
*/
$root_path = "";


/*** Do NOT modify anything below this line ***/

/** Setup default functions, variables, constants **/
define( 'DS' , DIRECTORY_SEPARATOR ); /* Shorten the directory separator */
function path_rewrite( $path ) { return preg_replace( '`(\\\|/)`' , DS , $path ); }
if ( empty( $root_path ) ) $root_path = dirname( __FILE__ ) . DS;
else {
	if ( !preg_match( '/(\\\|/)$/' , $root_path ) ) {
		$root_path .= DS;
	}
	$root_path = path_rewrite( $root_path );
}

/** verify if core.php could be found **/
if ( !is_file( $root_path . 'Core.php' ) ) {
	print "Error: Framework Core.php could not be found within $root_path";
}

/** Continue defining constants **/
define( 'FW_ROOT_PATH' , $root_path );

/** Clean up **/
unset( $root_path );

require FW_ROOT_PATH . 'Core.php';