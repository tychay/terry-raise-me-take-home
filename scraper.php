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
 * Goes to a URL endpoint and puts data as JSON
 * @param  string $url The URL to hit
 * @param  mixed $data data to put in body as JSON
 * @return mixed json value or false if failed
 */
function putUrlJson($url,$data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) );
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

/**
 * [putDataInElastic description]
 *
 * curl -XPUT "http://localhost:9200/movies/movie/1" -d'
{
    "title": "The Godfather",
    "director": "Francis Ford Coppola",
    "year": 1972,
    "genres": ["Crime", "Drama"]
}'
 * @param  [type] $student_obj [description]
 * @return [type]              [description]
 */
function putDataInElastic($student_id, $student_obj) {
	$base_url = 'http://localhost:9200/raiseme/student/';
	$url = $base_url . urlencode($student_id);
	$data = (putUrlJson($url, $student_obj));
	print_r($data);
}

$student_ids = getUrlJson('http://raise-me-take-home.raise.me');

// This stores list of unique fields for student object (outside of courses)
$student_fields = Array();
// This stores list of unique fields for courses
$course_fields = Array();

// temporary counter to not spam the API
//$i = 0;

foreach ($student_ids as $student_id) {
	$student_obj = getStudent($student_id);
	// I can iterate over objects
	foreach ($student_obj as $student_key=>$student_value) {
		if ( $student_key == 'courses' ) {
			$grade_total = 0.0;
			$grade_num = 0;
			foreach ($student_value as $course_obj) {
				foreach ($course_obj as $course_key=>$course_value) {
					if( !in_array($course_key, $course_fields) ){
        				$course_fields[] = $course_key;
			        }
				}
				if ($course_obj->gradeValue) {
					$grade_total += $course_obj->gradeValue;
					++$grade_num;
				} elseif ($course_obj->grade) {
					// TODO: this should be more robust in cases there is A+ B-, etc.
					switch ($course_obj->grade) {
						case 'A':
							$grade_total += 4.0;
							++$grade_num;
							break;
						case 'B':
							$grade_total += 3.0;
							++$grade_num;
							break;
						case 'C':
							$grade_total += 2.0;
							++$grade_num;
							break;
						case 'D':
							$grade_total += 1.0;
							++$grade_num;
							break;
						case 'F':
							$grade_total += 0;
							++$grade_num;
							break;
					}
				} // Else course is incomplete
			}
			// save grade to object
			if ( $grade_num > 0 ) {
				$student_obj->GPA = $grade_total / $grade_num;
			}
			//var_dump($student_obj->GPA);
			continue; // don't put the courses inside $student_fields !
		}

		// find student fields
		if ( !in_array($student_key, $student_fields) ) {
			$student_fields[] = $student_key;
		}

		//print_r($student_obj);
	}

	// INSERT DATA INTO ELASTICSEARCH
	putDataInElastic($student_id, $student_obj);

	//++$i;
	//if ($i == 10) { break; }
}

echo "STUDENT FIELDS = ";
print_r($student_fields);
echo "COURSE FIELDS = ";
print_r($course_fields);

?>