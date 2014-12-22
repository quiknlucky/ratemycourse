<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<?php 
		//connect to the database
		include('database.php');
	?>
</head>
<body>
	<?php
	
		//put values passed to page in variables
		$sub_subject = $_GET['choose_subject'];
		$sub_course = $_GET['choose_course'];
		$sub_term = $_GET['choose_term'];
		 
		if(isset($sub_subject) && isset($sub_course) && isset($sub_term)){
			
			//get details about all sections of selected course in selected term
			//select instructor, meeting time and location
			$section_query = "select concat(s.subject,' ',s.course_number,'-',s.section_number) course,        
									 concat(i.first_name,' ',i.last_name) inst,
									 concat(m.monday,m.tuesday,m.wednesday,m.thursday,m.friday) days,
									 concat(TIME_FORMAT(STR_TO_DATE(m.begin_time, '%H%i'), '%l:%i %p'),'-',
											TIME_FORMAT(STR_TO_DATE(m.end_time, '%H%i'), '%l:%i %p')) meeting,
									 concat(m.building,' ',m.room) location,
									 s.crn crn
							  from section s, 
								   meeting m, 
								   instructor i, 
								   section_instructor n
							  where s.term = m.term
							  and s.crn = m.crn
							  and s.term = n.term
							  and s.crn = n.crn
							  and n.id = i.id
							  and s.subject = '".$sub_subject."'
							  and s.course_number = '".$sub_course."'
							  and s.term = '".$sub_term."'";
			$result_section = mysqli_query($db, $section_query);
			
			//print html to display section select control
			echo "<h3>Section:</h3>";
			echo "<label>";
			echo "<select class='form-control review_details' name='choose_section'>";
			echo "<option selected='selected' value=''>---------</option>";	
			while($row = mysqli_fetch_array($result_section)){ 
				echo "<option value='".$row['crn']."'>".$row['course']." ".$row['inst']." ".$row['days']." ".$row['meeting']." ".$row['location']."</option>";
			}
			echo "</select>";
			echo "</label>";
			//echo"<label for='choose_section' class='error'></label>";
		}
	?>
</body>
</html>