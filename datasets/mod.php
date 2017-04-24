<?php

$file1 = 'entering.txt';
$file2 = 'graduating.txt';

//string file_get_contents ( string $filename [, bool $use_include_path = false [, resource $context [, int $offset = 0 [, int $maxlen ]]]] )
//
/**
 * Returns counts (instances) of each line as an array keyed by line in a file
 * @param  string $filename [description]
 * @return array
 */
function read_file_into_array_count($filename) {
	$data = file_get_contents($filename);
	$data_lines = explode("\n", $data);
	$counts = array();
	foreach ($data_lines as $line_txt) {
		if (!$line_txt) { continue; }
		if ( array_key_exists($line_txt,$counts) ) {
			++$counts[$line_txt];
		} else {
			$counts[$line_txt] = 1;
		}
	}
	return $counts;
}

$entering_array = read_file_into_array_count($file1,4);
ksort($entering_array);
$leaving_array = read_file_into_array_count($file2,4);
ksort($leaving_array);

$enrollment = Array();
$current_count = 0;
foreach ($entering_array as $date=>$count) {
	$current_count += $count;
	$enrollment[$date] =  $current_count;
}
//print_r($enrollment); print_r($leaving_array); die;
$current_count = 0;
$last_enrollment = 0;
$i = 0;
foreach ($leaving_array as $date=>$count) {
	++$i;
	$current_count += $count;
	if ( array_key_exists($date, $enrollment) ) {
		$last_enrollment = $enrollment[$date];
	}
	$enrollment[$date] =  $last_enrollment - $current_count;
	$last_enrollment = $enrollment[$date];
	//print_r(array($current_count, $count, $enrollment[$date], $last_enrollment));
	//if ($i >= 5) die;
}

print_r($enrollment);

?>