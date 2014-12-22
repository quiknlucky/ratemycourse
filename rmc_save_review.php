<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<?php 
		//connect to the database
		include('database.php');
	?>
	
	<script>	
	
		//when button to return to homepage is click this function is called
		function return_main(){
			$.ajax({
				url: "ratemycourse.php",
				success: function(msg){
					$("#page_body").html(msg);
				},
				error:function(){
					$("#page_body").empty().append('something went wrong');
				}
			});
		}	
	</script>
	
	<style>	
		#button_return { padding: 5px 0 5px 0; margin: 50px 0 15px 0;}
		#main_content { padding-top: 50px;}	
	</style>
</head>
<body>
	<main id="main_content" class="container">
		<?php
		
			//put values entered in form passed to this page in variables
			$sub_major = $_POST['choose_major'];
			$sub_term = $_POST['choose_term'];
			$sub_crn = $_POST['choose_section'];
			$sub_rating = $_POST['rating'];
			$sub_review = $_POST['review'];
			$sub_weekly_time = $_POST['weekly_time'];
			$sub_difficulty = $_POST['difficulty'];
			$sub_gpa = $_POST['gpa'];
			$sub_age = $_POST['age'];
			$sub_gender = $_POST['gender'];
			
			if(isset($sub_term) && isset($sub_crn)){
			
				//select course from selected section to display to user on confirmation page
				$result = mysqli_query($db, "SELECT concat(subject,' ',course_number) AS class FROM section WHERE term = '".$sub_term."' AND crn = '".$sub_crn."'");
				while($row = mysqli_fetch_array($result)){
					$sub_course = $row['class'];
				}
				
				//build the statement to insert review values into the review table
				$review_query = "INSERT INTO review (term, crn, rating, review, time_spent, difficulty)
								 VALUES ('".$sub_term."', '".$sub_crn."', '".$sub_rating."', '".$sub_review."', '".$sub_weekly_time."', '".$sub_difficulty."')";

				//execute the insert statement
				$insert_review = mysqli_query($db, $review_query);

				//build the statement to update the student table with student values
				//after the review insert statement a trigger executes and inserts the review_id
				//into the student table, the update statement finds the maximum review_id 
				//which is the one just inserted by the trigger and update that row with the
				//student values
				$student_query = "UPDATE student 
								  INNER JOIN review ON student.review_id = review.review_id
								  SET major_code = '".$sub_major."',
									  gpa = '".$sub_gpa."',
									  age = '".$sub_age."',
									  gender = '".$sub_gender."'
								  WHERE student.review_id = (select max(review.review_id)
															 from review)";
				
				//execute the update statement
				$insert_student = mysqli_query($db, $student_query);
				
				//if insert or update statements fail the variables will be null 
				if (!$insert_review || !$insert_student) {
					echo "<h2 class='bg-danger'>Review for ".$sub_course." Could Not be Saved</h2>";
				}
				else {
					echo "<h2 class='bg-success'>Review for ".$sub_course." Saved Successfully</h2>";
				}
			}		
			
		?>
		
		<!-- success or failure message is displayed and user must go back to home page, html to display button -->
		<div id="button_return">
			<button type="button" class="btn btn-info btn-lg" onclick="return_main()">Return to Homepage</button>
		</div>
	
		<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
	
	</main>
</body>
</html>