<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<?php 
		//connect to the database
		include('database.php');
	?>
	
	<script>
		
		//on ready function that looks for changes in select controls
		$(function(){
		
			//when user uses course select, send course to rmc_reviews to execute query
			//display results in the #fill_reviews div
			$('#choose_course').bind('change', function(event){

				var values = $(this).val();
				$.ajax({
					url: "rmc_reviews.php",
					type: "GET",
					data: {choose_course: values},
					success: function(msg){
						$("#fill_reviews").html(msg);
					},
					error:function(){
						$("#fill_reviews").empty().append('something went wrong');
					}
				});
			});
		});
	
	</script>
</head>
<body>
	<?php

	//put values passed to page in variables
	$sub_subject = $_GET['choose_subject'];
	$sub_dept = $_GET['choose_dept'];
	 
	if(isset($sub_subject) || isset($sub_dept)){
	
		//this page is called by both the department and the subject select controls
		//check if is the subject control was used
		if (!is_null($sub_subject)){
			$result = mysqli_query($db, "SELECT concat(subject,' ',course_number) AS class, title FROM course WHERE subject = '".$sub_subject."' ORDER BY course_number ASC");
		} 
		//if subject was not used assume it was the department
		else {
			$result = mysqli_query($db, "SELECT concat(subject,' ',course_number) AS class, title FROM course WHERE department_code = '".$sub_dept."' ORDER BY subject, course_number ASC");
		}
		
		//print html to display course select control based on previous selection
		echo "<h3>Select a Course:</h3>";
		echo "<div class='select_course_list'>";
		echo "<select class='form-control' id='choose_course'>";
		echo "<option selected='selected' value=''>---------</option>";
		while($row = mysqli_fetch_array($result)){
			echo "<option value='".$row['class']."'>".$row['class']." - ".$row['title']."</option>";
		}
		echo "</select>";
		echo "</div>";
	}
?>
</body>
</html>