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
			
			$('#choose_major').bind('change', function(event){

				var values = $(this).val();
				$.ajax({
					url: "rmc_category.php",
					type: "GET",
					data: {selected_major: values},
					success: function(msg){
						$("#fill_course").html(msg);
					},
					error:function(){
						$("#fill_course").empty().append('something went wrong');
					}
				});
			});
		});
	
	</script>
</head>
<body>
	<?php

	//put value passed to page in a variable
	$sub_category = $_GET['choose_category'];
	$sub_major = $_GET['selected_major'];

	if(isset($sub_category) || isset($sub_major)){
	
		//check which category was selected
		//this query returns the top 10 highest overall rated courses
		if ($sub_category == 'review_overall'){
			$query_overall = "SELECT *
							  FROM review_overall";
			$result = mysqli_query($db, $query_overall);
		}
		//this query returns the top 10 courses with the most reviews of easy and very easy for difficulty
		else if ($sub_category == 'review_easiest'){
			$query_easiest = "SELECT *
			                  FROM review_easiest";
			$result = mysqli_query($db, $query_easiest);
		}
		//this query returns the top 10 rated courses that met on mon, wed, and fri
		else if ($sub_category == 'review_mwf'){
			$query_mwf = "CALL review_by_days('M','','W','','F')";
			$result_mwf = mysqli_multi_query($db, $query_mwf);
			$result = mysqli_store_result($db);
		}
		//this query returns the top 10 rated courses that met on tues and thurs
		else if ($sub_category == 'review_tr'){
			$query_tr = "CALL review_by_days('','T','','R','')";
			$result_tr = mysqli_multi_query($db, $query_tr);
			$result = mysqli_store_result($db);
		}
		//this query returns the top 10 rated courses by male students
		else if ($sub_category == 'review_male'){
			$query_male = "select CONCAT(s.subject, ' ', s.course_number) AS class,
								  c.title AS title,
							      ROUND(AVG(r.rating), 1) AS calc
						   from review r, student u, section s, course c
						   where r.review_id = u.review_id
						   and s.subject = c.subject
						   and s.course_number = c.course_number
						   and r.crn = s.crn
						   and r.term = s.term
						   and u.gender = 'Male'
						   group by class, title
						   order by calc desc
						   limit 10";
			$result_male = mysqli_multi_query($db, $query_male);
			$result = mysqli_store_result($db);
		}
		//this query returns the top 10 rated courses by female students
		else if ($sub_category == 'review_female'){
			$query_female = "select CONCAT(s.subject, ' ', s.course_number) AS class,
								    c.title AS title,
									ROUND(AVG(r.rating), 1) AS calc
							 from review r, student u, section s, course c
							 where r.review_id = u.review_id
							 and s.subject = c.subject
							 and s.course_number = c.course_number
							 and r.crn = s.crn
							 and r.term = s.term
							 and u.gender = 'Female'
							 group by class, title
							 order by calc desc
							 limit 10";
			$result_female = mysqli_multi_query($db, $query_female);
			$result = mysqli_store_result($db);
		}
		//this query returns the top 10 rated courses by select major
		else if (isset($sub_major)){
			$query_major = "select CONCAT(s.subject, ' ', s.course_number) AS class,
							       c.title AS title,
								   ROUND(AVG(r.rating), 1) AS calc
							from review r, student u, section s, course c
							where r.review_id = u.review_id
							and s.subject = c.subject
							and s.course_number = c.course_number
							and r.crn = s.crn
							and r.term = s.term
							and u.major_code = '".$sub_major."'
							group by class, title
							order by calc desc
							limit 10";
			$result_major = mysqli_multi_query($db, $query_major);
			$result = mysqli_store_result($db);
		}
		
		//print html to display course select control
		if ($sub_category != 'review_major'){
			echo "<h3>Select a Course:</h3>";
			echo "<div class='select_course_list'>";
			echo "<select class='form-control' id='choose_course'>";
		}
		
		//if highest rated course was select print those courses
		if ($sub_category == 'review_overall'){
			echo "<option selected='selected' value=''>--Average Review by All Students--</option>";
			while($row = mysqli_fetch_array($result)){
				echo "<option value='".$row['class']."'>".$row['class']." - ".$row['title']." : ".$row['calc']." Average</option>";
			}
		}
		//if easiest course was selected print those courses
		else if ($sub_category == 'review_easiest'){
			echo "<option selected='selected' value=''>--Number of Students indicating Easy or Very Easy--</option>";
			while($row = mysqli_fetch_array($result)){
				echo "<option value='".$row['class']."'>".$row['class']." - ".$row['title']." : ".$row['calc']." Students</option>";
			}
		}
		//if mwf course was selected print those courses
		else if ($sub_category == 'review_mwf'){
			echo "<option selected='selected' value=''>--Average Review of MWF Courses--</option>";
			while($row = mysqli_fetch_row($result)){
				echo "<option value='".$row['0']."'>".$row['0']." - ".$row['1']." : ".$row['2']." Average</option>";
			}
		}
		//if tr course was selected print those courses
		else if ($sub_category == 'review_tr'){
			echo "<option selected='selected' value=''>--Average Review of TR Courses--</option>";
			while($row = mysqli_fetch_row($result)){
				echo "<option value='".$row['0']."'>".$row['0']." - ".$row['1']." : ".$row['2']." Average</option>";
			}
		}
		//if male rated course was selected print those courses
		else if ($sub_category == 'review_male'){
			echo "<option selected='selected' value=''>--Average Review of Male Rated Courses--</option>";
			while($row = mysqli_fetch_row($result)){
				echo "<option value='".$row['0']."'>".$row['0']." - ".$row['1']." : ".$row['2']." Average</option>";
			}
		}
		//if female rated course was selected print those courses
		else if ($sub_category == 'review_female'){
			echo "<option selected='selected' value=''>--Average Review of Female Rated Courses--</option>";
			while($row = mysqli_fetch_row($result)){
				echo "<option value='".$row['0']."'>".$row['0']." - ".$row['1']." : ".$row['2']." Average</option>";
			}
		}
		//if major rated courses was selected display list of majors
		else if ($sub_category == 'review_major'){
			echo "<h3>Select a Major:</h3>";
			echo "<div class='select_major_list'>";
			echo "<select id='choose_major' class='form-control' name='stu_major_list'>";
			echo "<option selected='selected' value=''>---------</option>";
			$select_major = mysqli_query($db, "SELECT m.major_code, m.major_description, count(r.review_id) review_count
											   FROM major m, review r, student s
											   WHERE m.major_code = s.major_code
											   AND r.review_id = s.review_id
											   GROUP BY m.major_code, m.major_description
											   ORDER BY m.major_description");
				while($row = mysqli_fetch_array($select_major)){ 
					echo"<option value='".$row['major_code']."'>".$row['major_description']." (".$row['review_count'].")</option>
					";
				}
			echo "</select>";
			echo "</div>";
		}
		//if major rated courses was selected print those courses
		else if (isset($sub_major)){
			echo "<option selected='selected' value=''>--Average Review of Courses By ".$sub_major." Majors--</option>";
			while($row = mysqli_fetch_row($result)){
				echo "<option value='".$row['0']."'>".$row['0']." - ".$row['1']." : ".$row['2']." Average</option>";
			}
		}
		echo "</select>";
		echo "</div>";
	}
?>
</body>
</html>