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
	//var_dump(sizeof($data_lines));
	// total in entering: int(15034)
	// total in leaving: int(15001)
	return $counts;
}

$entering_array = read_file_into_array_count($file1);
//http://php.net/ksort
ksort($entering_array);
//uksort($entering_array, "strnatcmp");
//print_r($entering_array);die;
$leaving_array = read_file_into_array_count($file2);
ksort($leaving_array);

//echo 'Entering = ';print_r($entering_array);
//echo 'Leaving = ';print_r($leaving_array);

$enrollment = Array();
$current_count = 0;
foreach ($entering_array as $date=>$count) {
	$current_count += $count;
	$enrollment[$date] =  $current_count;
}
//print_r($enrollment);
// ends with 15033 which is correct (1 line is blank)
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

//print_r($enrollment);
// find month of largest enrollment
arsort($enrollment);
reset($enrollment);
print_r(key($enrollment));


//$ cat entering.txt | grep '1864 Winter' | wc -l
//      45
//$ cat graduating.txt | grep '1864 Winter' | wc -l
//      39
//$ cat entering.txt | grep '1865 Winter' | wc -l
//     105
//$ cat graduating.txt | grep '1865 Winter' | wc -l
//     114
?>