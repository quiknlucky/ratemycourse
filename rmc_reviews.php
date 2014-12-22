<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<?php 
		//connect to the database
		include('database.php');
	?>
	
	<script src="../Chart.js"></script>
	
</head>
<body>
	<?php
		
		//put value passed to page in a variable
		$sub = $_GET['choose_course'];
		if(isset($sub)){
			
			//value passed in was in the form 'Subject Course_number' (eg 'CSCI 111')
			//need to separate the subject from the course_number in order to run the query 
			$sub_array = explode(' ',$sub);
			$sub_subject = $sub_array[0];
			$sub_course = $sub_array[1];
			
			//execute query based on selected course
			//get all review and students information for that course
			$course_reviews_result = mysqli_query($db, "select review.*, student.*
										  from review, section, student
										  where review.crn = section.crn
											and review.term = section.term
											and review.review_id = student.review_id
											and section.subject = '".$sub_subject."'
											and section.course_number = '".$sub_course."'
											order by review.term desc, review.date desc");
			//get all general details for that course
			$sub_course_details = mysqli_query($db, "select department.department_description, course.*
											   from course, department
											   where subject = '".$sub_subject."'
											   and course_number = '".$sub_course."'
											   and course.department_code = department.department_code");
			
			while($row = mysqli_fetch_array($sub_course_details)){
				
				//assign all general course details to variables
				$title = $row['title'];
				$description = $row['description'];
				$credits = $row['credits'];
				$lecture_only = $row['lecture_only'];
				$prerequisite = $row['prerequisite'];
				$multiple_sections = $row['multiple_sections'];
				$department_description = $row['department_description'];

	?>
				<!-- html display general course details -->
				<input hidden disabled="disabled" type="text" id="hidden_course" value="<?=$sub_subject.' '.$sub_course?>">
				<h3><?=$title?></h3>
				<dl id="course_details">
				<dt>Department:</dt>
				<dd><?=$department_description?></dd>
				<dt>Credits:</dt>
				<dd><?=$credits?></dd>
				<dt>Lecture Only:</dt>
				<dd><?=$lecture_only?></dd>
				<dt>Prerequisite:</dt>
				<dd><?=$prerequisite?></dd>
				<dt>Multiple Sections:</dt>
				<dd><?=$multiple_sections?></dd>
				<blockquote><?=$description?></blockquote>
				</dl>		
				
	<?php
			}
			//if no reviews exist for select course display this 
			if (mysqli_num_rows($course_reviews_result)==0){
				echo"<h3>No Reviews Exist for this course</h3>";
				echo"<h4><input type='submit' class='btn btn-primary' onclick='write_review()' value='Write the First Review'></h4>";
			} 
			//if reviews do exist for this course display those reviews
			else{
				//display option to write a new review
				echo "<h3 id='review_header'>Reviews <input type='submit' class='btn btn-primary' onclick='write_review()' value='Write a Review'></h3>";
				
				//find the number of reviews and the average of them for the selected course
				$review_totals = mysqli_query($db, "SELECT round(avg(rating),1) average, count(rating) count
												  FROM review, section
												  WHERE review.term = section.term
												  AND review.crn = section.crn
												  AND section.subject = '".$sub_subject."'
												  AND section.course_number = '".$sub_course."'");
				
				//assign selected count and average to variables
				while($totals_row = mysqli_fetch_array($review_totals)){
					$total_avg = $totals_row['average'];
					$total_count = $totals_row['count'];
				}
				
				//display average of reviews and count
				echo "<h4 id='total_info' class='bg-info'>".$total_avg." Average from ".$total_count." Reviews</h4>";
				
				//TODO: loop to output sums of how many of each rating there are
				//ex: *****  6
				//      ****   9
				//      ***     3
				//      **       2
				//      *         4
				
				//query to find avg of reviews over time 
				$result_rating = mysqli_query($db, "select substring_index(t.term_description,' ',2) AS term,
																round(avg(r.rating),1) AS rating
																from term t, review r, section s
																where s.term = t.term_code
																and r.term = s.term
																and r.crn = s.crn
																and s.subject = '".$sub_subject."'
																and s.course_number = '".$sub_course."'
																group by t.term_description");
				//loop to put values into term array and rating array
				while($rating_row = mysqli_fetch_array($result_rating)){
					$stu_term[] = $rating_row['term'];
					$stu_rating[] = $rating_row['rating'];
				}
				//convert arrays into data usable by chart
				$label_query = json_encode(($stu_term),true); 
				$data_query = json_encode(($stu_rating),true); 
	?>

				<!-- TODO: clean up js in the middle of php -->
				<!-- js to setup chart to display reviews over time -->
				<script>
					var label_query = <?php echo $label_query; ?>;
					var data_query = <?php echo $data_query; ?>;
					var data = {
						labels: label_query,
						datasets: [
							{
								label: "My First dataset",
								fillColor: "rgba(0,51,79,0.5)",
								strokeColor: "rgba(1,164,251,0.9)",
								highlightFill: "rgba(0,0,0,0.75)",
								highlightStroke: "rgba(0,0,0,1)",
								data: data_query
							}
						]
					};			
					var ctx = document.getElementById("myChart").getContext("2d");
					window.myLine = new Chart(ctx).Line(data, {
						// Boolean - If we want to override with a hard coded scale
						scaleOverride: true,
						// Number - The number of steps in a hard coded scale
						scaleSteps: 10,
						// Number - The value jump in the hard coded scale
						scaleStepWidth: 0.5,
						// Number - The scale starting value
						scaleStartValue: 0
					});
				</script>
				
				<canvas id="myChart" width="250" height="250"></canvas>
	<?php
				
				//loop to display all reviews for the course
				while($row = mysqli_fetch_array($course_reviews_result)){
					
					//find the major description for the student that made the current review
					$stu_major_code = $row['major_code'];
					$result_major = mysqli_query($db, "SELECT major_description FROM major WHERE major_code = '".$stu_major_code."'");
					while($major_row = mysqli_fetch_array($result_major)){
						$stu_major = $major_row['major_description'];
					}
					
					$term_code = $row['term'];
					$result_term = mysqli_query($db, "SELECT term_description FROM term WHERE term_code = '".$term_code."'");
					while($term_row = mysqli_fetch_array($result_term)){
						$stu_term = $term_row['term_description'];
					}
					
					//assign all selected review and student details for this review to variables
					$difficulty = $row['difficulty'];
					$time_spent = $row['time_spent'];
					$rating = $row['rating'];
					$crn = $row['crn'];
					$review = $row['review'];
					$review_date = strtotime($row['date']);
					$formatted_date = date('jS F Y', $review_date);
					$gpa = $row['gpa'];
					$age = $row['age'];
					$gender = $row['gender'];			
					
					//find the name of the instructor for the specific section this student took
					$result_instructor = mysqli_query($db, "SELECT concat(first_name,' ',last_name) name
													  FROM instructor i, section_instructor s 
													  WHERE s.term = '".$term_code."'
													  AND s.crn = '".$crn."'
													  AND s.id = i.id");
					
					//assign selected instructor name to variable
					while($inst_row = mysqli_fetch_array($result_instructor)){
						$instructor = $inst_row['name'];
					}
			
			
	?>
					<!-- html to display the current review -->
					<article>
						<form action="">
							<fieldset class="rating">

								<input disabled="disabled" type="radio" <?php if ($rating == '5') { echo "checked"; } ?> id="star5" name="rating" value="5" />
								<label for="star5" title="Rocks!">5 stars</label>
								<input disabled="disabled" type="radio" <?php if ($rating == '4') { echo "checked"; } ?> id="star4" name="rating" value="4" />
								<label for="star4" title="Pretty good">4 stars</label>
								<input disabled="disabled" type="radio" <?php if ($rating == '3') { echo "checked"; } ?> id="star3" name="rating" value="3" />
								<label for="star3" title="Meh">3 stars</label>
								<input disabled="disabled" type="radio" <?php if ($rating == '2') { echo "checked"; } ?> id="star2" name="rating" value="2" />
								<label for="star2" title="Kinda bad">2 stars</label>
								<input disabled="disabled" <?php if ($rating == '1') { echo "checked"; } ?> type="radio" id="star1" name="rating" value="1" />
								<label for="star1" title="Sucks big time">1 star</label>
							</fieldset>
						</form>

						<hr style="clear:both;">
						<small class="pull-right"><?=$formatted_date?></small>
						<blockquote><?=$review?></blockquote>
						<dl id="review_info">
						<dt>Instructor:</dt>
						<dd><?=$instructor?></dd>
						<dt>Term Taken:</dt>
						<dd><?=$stu_term?></dd>
						<dt>Weekly time:</dt>
						<dd><?=$time_spent?> hours</dd>
						<dt>Difficulty:</dt>
						<dd><?=$difficulty?></dd>
						</dl>
						<dl id="student_info">
						<dt>Major:</dt>
						<dd><?=$stu_major?></dd>
						<dt>GPA:</dt>
						<dd><?=$gpa?></dd>
						<dt>Age:</dt>
						<dd><?=$age?></dd>
						<dt>Gender:</dt>
						<dd><?=$gender?></dd>
						</dl>
					</article>
	  
	<?php
	    
	  
				}
			}
		}
	?>
</body>
</html>