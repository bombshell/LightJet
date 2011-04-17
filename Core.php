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

/** Define static default values **/
define( 'FW_NAME'        , 'LightJet' );
define( 'FW_VERSION'     , '0.2.0' );
define( 'FW_COPY_STRING' , FW_NAME . ' (c) 2011 Bombshellnet.org Ver. ' . FW_VERSION );
define( 'FW_COPY_HTML'   , preg_replace( '`(\(c\))`', '&copy;' , FW_COPY_STRING ) );
define( 'FW_PATH_CONFIG' , FW_ROOT_PATH . 'Config' . DS );
define( 'FW_PATH_LIB'    , FW_ROOT_PATH , 'Library' . DS );
define( 'DATE_SYSTEM' , date( 'r' ) );
define( 'DATE' , date( 'D, M d Y h:i:s A O ') );

/** Various information about the system **/
if ( substr( PHP_OS, 0, 3 ) == 'WIN' ) {
	define( 'FW_OS' , 'MS_Windows' );
} elseif ( strcasecmp( substr( PHP_OS, 0, 5 ) , 'LINUX' ) == 0 ) {
	define( 'FW_OS' , 'Linux' );
} else {
	define( 'FW_OS' , 'Unknown' );
}

/** Slight modification to PHP settings **/
$include_path = ini_get( 'include_path' );
if ( FW_OS == 'MS_Windows' ) {
	$include_path .= ';';
} else {
	$include_path .= ":";
}
ini_set( 'include_path' , $include_path .= FW_ROOT_PATH );

/** Now lets load the framework **/
require FW_PATH_LIB . 'functions.php';
require FW_PATH_BASE . path_rewrite( 'interface/Default.Class.php' );
$sapi = php_sapi_name();
require FW_PATH_BASE . path_rewrite( 'interface/' ) . ( $sapi == 'cli' || $sapi == 'embed' ) ? 'Cli.Class.php' : 'Web.Class.php';