<?php

namespace Typemill\Static;

use Typemill\Models\StorageWrapper;

class Helpers{

	public static function urlInfo($uri)
	{
		$basepath 			= preg_replace('/(.*)\/.*/', '$1', $_SERVER['SCRIPT_NAME']);
		$currentpath  		= $uri->getPath();
		$route 				= str_replace($basepath, '', $currentpath);
		$scheme 			= $uri->getScheme();
		$authority 			= $uri->getAuthority();
		$protocol 			= ($scheme ? $scheme . ':' : '') . ($authority ? '//' . $authority : '');
		$baseurl 			= $protocol . $basepath;
		$currenturl  		= $protocol . $currentpath;

		return [
			'basepath' 		=> $basepath,
			'currentpath' 	=> $currentpath,
			'route' 		=> $route,
			'scheme' 		=> $scheme,
			'authority' 	=> $authority,
			'protocol' 		=> $protocol,
			'baseurl' 		=> $baseurl,
			'currenturl' 	=> $currenturl
		];
	}

	public static function getUserIP()
	{
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP))
		{
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
		{
			$ip = $forward;
		}
		else
		{
			$ip = $remote;
		}

		return $ip;
	}


	public static function addLogEntry($action)
	{
		$line 		= self::getUserIP();
		$line 		.= ';' . date("Y-m-d H:i:s");
		$line 		.= ';' . $action;

		$storage 	= new StorageWrapper('\Typemill\Models\Storage');
		$logfile 	= $storage->getFile('dataFolder', 'security', 'securitylog.txt');

		if($logfile)
		{
			$logfile .= $line . PHP_EOL;
		}
		else
		{
			$logfile = $line . PHP_EOL;
		}
		
		$result = $storage->writeFile('dataFolder', 'security', 'securitylog.txt', $logfile);

		if($result)
		{
			return true;
		}

		return $storage->getError();
	}

	public static function array_sort($array, $on, $order=SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();

	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }

	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }

	        foreach ($sortable_array as $k => $v) {
	            $new_array[] = $array[$k];
			}
		}

		return $new_array;
	}

	public static function printTimer($timer)
	{
		$lastTime = NULL;
		$table = '<html><body><table>';
		$table .= '<tr><th>Breakpoint</th><th>Time</th><th>Duration</th></tr>';
		foreach($timer as $breakpoint => $time)
		{
			$duration = $time - $lastTime;
			
			$table .= '<tr>';
			$table .= '<td>' . $breakpoint . '</td>';
			$table .= '<td>' . $time . '</td>';
			$table .= '<td>' . $duration . '</td>';
			$table .= '</tr>';
			
			$lastTime = $time;
		}
		$table .= '</table></body></html>';
		echo $table;
	}	
}