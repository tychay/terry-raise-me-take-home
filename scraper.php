<?php

/**
 * Goes to a URL endpoint and returns the HTTP body as a JSON object
 * @param  string $url The URL to hit
 * @return mixed json value or false if failed
 */
function getUrlJson($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ($httpcode>=200 && $httpcode<300) ? json_decode($data) : false;
}

/**
 * Goes to the student URL endpoint and grabs the data from it
 * @param  string $student_id Student Id
 * @return Object|false
 */
function getStudent($student_id) {
	$url = 'http://raise-me-take-home.raise.me/'.urlencode($student_id).'.json';
	return getUrlJson($url);
}


$student_ids = getUrlJson('http://raise-me-take-home.raise.me');
$student_fields = Array();
$course_fields = Array();
$i = 0;

foreach ($student_ids as $student_id) {
	$student_data = getStudent($student_id);
	// I can iterate over objects
	foreach ($student_data as $key=>$value) {
		if ( $key == 'courses' ) {
			foreach ($value as $course_obj) {
				foreach ($course_obj as $course_key=>$course_value) {
					if( !in_array($course_key, $course_fields) ){
        				$course_fields[] = $course_key;
			        }
				}
			}
			continue; // don't put the courses inside $student_fields !
		}
		if ( !in_array($key, $student_fields) ) {
			$student_fields[] = $key;
		}
	}
	++$i;
	if ($i == 10) { break; }
}
echo "STUDENT FIELDS = ";
print_r($student_fields);
echo "COURSE FIELDS = ";
print_r($course_fields);

?>