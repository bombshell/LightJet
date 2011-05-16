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
 * @Function 
 * PHP error handler 
 * @return (NULL)  
 */ 
function PHP_ERROR_MANAGER( $errno, $errmsg, $filename, $linenum, $vars ) 
{ 
	/* retrieve global variables */ 
	$cfg = $GLOBALS[ 'cfg' ];  
	/* timestamp for the error entry */ 
	$dt = date("Y-m-d H:i:s (T)");  
	/* if error has been supressed with an @ */ 
	if (error_reporting() == 0) { 
		/*return; */ 		
	}  
	
	/* define an assoc array of error string  in reality the only entries we should  consider are E_WARNING, E_NOTICE, E_USER_ERROR,  E_USER_WARNING and E_USER_NOTICE */ 
	@$errortype = array ( E_ERROR=> 'Error', 
						  E_WARNING=> 'Warning', 
						  E_PARSE=> 'Parsing Error', 
						  E_NOTICE => 'Notice', 
						  E_CORE_ERROR => 'Core Error', 
						  E_CORE_WARNING => 'Core Warning', 
						  E_COMPILE_ERROR=> 'Compile Error', 
						  E_COMPILE_WARNING=> 'Compile Warning', 
						  E_USER_ERROR => 'User Error', 
						  E_USER_WARNING => 'User Warning', 
						  E_USER_NOTICE=> 'User Notice', 
						  E_STRICT => 'Runtime Notice', 
						  E_RECOVERABLE_ERROR=> 'Catchable Fatal Error' );  
	switch( $errno ) { 
		case E_ERROR; 
		case E_PARSE; 
		case E_WARNING; 		
		case E_USER_ERROR; 
		case E_USER_WARNING; 		
			$err = "Date: $dt ErrorNo: $errno ErrorType: {$errortype[$errno]} ErrorMsg: $errmsg File: $filename Line: $linenum"; 
			/* log error */ 
			z_error_log( 'ERR0901' , $err ); 
			/* redirect user to error */ 
			if ( !headers_sent() ) { 		 
				if ( __DEBUG__ === true ) { 		 
					$url = SERVER_HOST . "servlet/error.php?id=ERR0901&Z_ERROR_SPEC=" . base64_encode( $err ); 		 
					header( 'Location: ' . $url ); } } 		
			else {  			
				print 'PHP Error: ' . $err; } 			
				
			if ( !empty( $cfg[ 'default_admin_email' ] ) ) { 			
				if ( @$cfg[ 'email_error_type' ] == 'critical' ) { 			
					$to = ''; 			
					$subject = 'Zsystem PHP Error Manager: PHP Error occurred'; 			
					$headers = 'From: ZsystemsErrorManager@' . SERVER_HOST . "\r\n" . 'To: ' . $cfg[ 'default_admin_email' ]; 			
					mail( $to , $subject, 'Do Not Reply To This E-Mail' . "\r\n \r\n" . 'PHP Error: ' . $err , $headers ); } } 		
					exit;  
		break; } 
}
		  
/** 
 * @category Function
 * @name filter_chars
 * Filter characters from a string   
 * @param (string) String  
 * @param (integer) Filter ID 
 *                  1 = Filter invalid E-Mail characters
 *                  2 = Filter invalid Filesystem characters
 *                  3 = Filter invalid URL characters
 *                  4 = Filter any non alphanumeric characters
 * @param (bool) True to replace invalid characters with spaces 
 * @return (string) Return filtered string
 *  
 */ 
function filter_chars( $str , $filterId=4, $replaceWithSpaces=false )  
{ 
	/* Series of checks */
	if ( !preg_match( '`[1-4]`' , $filterId ) ) {
		$charsId = 4;
	}
	
	/* email filter */
	$chars[1] = array('"','\'','`','!','#','$','%','^','&','*','(',')','+','=','-',':',';','<','>',',','?','/','\\','{','}','[',']');
	/* filesystem filter */
	$chars[2] = array('"','\'','`','!','@','#','$','%','^','&','*','(',')','+','=','-',':',';','<','>',',','?','{','}','[',']','~',':','.');
	/* url filter */
	$chars[3] = array('"','\'','`','!','@','#','$','%','^','*','(',')','+','-',';','.',',','\\','{','}','[',']','~');
	/* filter all invalid characters */
	$chars[4] = array('"','\'','`','!','#','$','%','^','&','*','(',')','+','=','-',':',';','<','>','.',',','?','/','\\','{','}','[',']','@','~');
	
	/* Default values */
	$replace = ( $replaceWithSpaces == true ) ? ' ' : '';	
	
	if ( !empty($str) ) { 
		/* keep chars to one character */ 
		if ( ( strlen($str)>1 ) ) {   
			$str = str_replace( $chars[$filterId] , $replace , $str );
		} 
	}
	return $str;
}      

/** 
 * @category Function 
 * @name trim_array_values
 * Trim array values and keys. To be used with array_walk
 * @example array_walk( $array , trim_array_values );
 * @param (string) Array value  
 * @param (string) Array key  
 * @return (NULL) 
 */ 
function trim_array_values( &$value, &$key )  
{ 
	$value = trim($value); $key = trim($key); 
}  




/**  
 * @Function  
 * Parses file, Translate given key => value  
 * @param (string) File  
 * @param (array) Contains array key => value  
 * @return (string) Return file contents 
 */ 
function Parse_File( $file , $searchArr=false, $specialOptions=false ) 
{  
	$str = file_get_contents( $file );  
	
	/* Object:HTML this is already declared check first */  
	if ( !function_exists( 'pattern' ) ) {  
		function pattern( $str ) 
		{  
			return "/(\/\*)?\[\/$str\/\](\*\/)?/is"; 
		} 
	}  
	
	$needles[] = pattern( 'time' );  $haystacks[] = DATE_SYS;     
	if ( !empty( $searchArr ) ) 
		foreach( $searchArr as $key => $val ) { 
			$needles[] = pattern( $key ); $haystacks[] = $val; 
		}  
	
	$str = preg_replace( $needles, $haystacks , $str );  return $str; 
}  

/**  
 * @Function 
 * this function will return RequestDispatcher url with the file to get, doesn't 
 * check if file exists, RequestDispatcher will check. 
 * @param (string) mode: image, js, css 
 * @return (string) Url 
 */ 
function Request_Dispatcher( $mode , $get ) 
{ 
	switch( $mode ) { 
		case "js"; 
		case "css"; 
		case "image"; 
			return SERVER_HOST . "RequestDispatcher/?mode=$mode&get=" . urlencode( $get ); 
		break; 
		default: 
			return false; 
		break; 
	} 
}  

