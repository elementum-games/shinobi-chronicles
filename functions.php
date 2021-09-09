<?php

/* 	File:	function.php
	Author: Levi Meahan
	Purpose: Holds general-purpose functions for use in multiple places.
*/


function timeRemaining($time_remaining, $format = 'short', $include_days = true, $include_seconds = true) {
	if($include_days) {
		$days = floor($time_remaining / 86400);
		$time_remaining -= $days * 86400;
	}
    else {
        $days = null;
    }
	
	$hours = floor($time_remaining / 3600);
	$time_remaining -= $hours * 3600;
	
	if($include_seconds) {
		$minutes = floor($time_remaining / 60);
		$time_remaining -= $minutes * 60;
		
		$seconds = $time_remaining;
	}
	else {
		$minutes = ceil($time_remaining / 60);
	}
	
	if($hours < 10 && $format == 'short') {
		$hours = '0' . $hours;
	}
	if($minutes < 10 && $format == 'short') {
		$minutes = '0' . $minutes;
	}
	if($include_seconds && $seconds < 10 && $format == 'short') {
		$seconds = '0' . $seconds;
	}
	
	$string = '';
	if($format == 'long') {
		if($days && $include_days) {
			$string = "$days day(s), $hours hour(s), $minutes minute(s)";
		}
		else if($hours && $hours != '00') {
			$string = "$hours hour(s), $minutes minute(s)";
		}
		else {
			$string = "$minutes minute(s)";
		}
		
		if($include_seconds) {
			$string .= ", $seconds seconds";
		}
	}
	else if($format == 'short') {
		if($days) {
			$string = "$days day(s), $hours:$minutes";
		}
		else if($hours && $hours != '00') {
			$string = "$hours:$minutes";
		}
		else {
			$string = "$minutes";
		}
		
		if($include_seconds) {
			$string .= ":$seconds";
		}
	}
	return $string;
}