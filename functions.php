<?php //commomn functions file for all php instances

function __e($element, $message){	
	if(!isset($_ENV['mode']) || $_ENV['mode'] !== "dev") return;
	$type = gettype($element);
	$suffix = capFirst($type);

	$function = "echo{$suffix}";
	$function($element, $message);
}
function echoString($string, $message)
{	
	if($message) echo "<br>".$message."<br>";
	echo "{$string} <br>";
}
function echoInteger($int, $message)
{	
	echo $message;
	echo "<br>{$int} <br>";
}
function echoBoolean($bool, $message)
{	
	if($message) echo "<br>".$message;
	echo "<br>{$bool} <br>";
}
function echoNull($null, $message)
{	
	if($message) echo "<br>".$message;
	echo "<br>{$null} is null<br>";
}

function echoArray($arr, $message)
{
	echo "<pre>";
		if($message) echo "<strong>".$message."</strong>";
		echo " has ".count($arr)." elements. \n";
			print_r($arr);
	echo "</pre>";
}

function echoObject($obj)
{
	$r 			= new ReflectionClass($obj);
	$properties = $r->getDefaultProperties();
	$methods 	= $r->getMethods();
	$divider = "==============================================================================================================================";

	$message = "= ".$r->getName().$r->getExtension()." ".$divider; 
	_e($message);
	_e($properties, "properties");
	_e($methods, "methods");

	echo $divider;
	
	_e("dump");
	echo "<pre>";
	var_dump($obj);
	echo "</pre>";
}
function capFirst($word)
{
	return substr_replace($word, strtoupper(substr($word, 0, 1)), 0, 1);
}

function isArray( $var )
{
	return gettype($var == 'array') ? true : false;
}
function isUnique($needle, $haystack)
{
	$mathces 		= array();
	foreach($haystack as $i => $value){
		//echo '$needle / $value is: '."{$needle} / {$value}<br>";
		if( $needle == $value){
			$matches[] = $value;
		}
	}
	
	$count = count($matches);

	$return_val = ($count >= 1) ? FALSE : TRUE;
	return $return_val;
}