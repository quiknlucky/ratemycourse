<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<?php 
		//connect to the database
		include('database.php');
	?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

	<script>
	
		//on ready function that looks for changes in select controls
		$(function() {
			
			//when user uses subject select, send subject code to rmc_courses to execute query
			//display results in the #fill_course div
			$('#choose_subject').bind('change', function(event){

				var values = $(this).val();
				$.ajax({
					url: "rmc_courses.php",
					type: "GET",
					data: {choose_subject: values},
					success: function(msg){
						$("#major_list").empty();
						$("#fill_course").html(msg);
					},
					error:function(){
						$("#fill_course").empty().append('something went wrong');
					}
				});
			});
		
			//when user uses department select send department code to rmc_courses to execute query
			//display results in the #fill_course div
			$('#choose_dept').bind('change', function(event){

				var values = $(this).val();
				$.ajax({
					url: "rmc_courses.php",
					type: "GET",
					data: {choose_dept: values},
					success: function(msg){
						$("#major_list").empty();
						$("#fill_course").html(msg);
					},
					error:function(){
						$("#fill_course").empty().append('something went wrong');
					}
				});
			});
			
			//when user uses category select send category code to rmc_category to execute query
			//display results in the #fill_course div, except when major category is chosen
			//display result in the #select_major_list div
			$('#choose_category').bind('change', function(event){

				var values = $(this).val();
				$.ajax({
					url: "rmc_category.php",
					type: "GET",
					data: {choose_category: values},
					success: function(msg){
						if (values == 'review_major'){
							$("#fill_course").empty();
							$("#major_list").html(msg);
						} else{
							$("#major_list").empty();
							$("#fill_course").html(msg);
						}
					},
					error:function(){
						$("#fill_course").empty().append('something went wrong');
					}
				});
			});
			
			$('#choose_major').bind('change', function(event){

				var values = $(this).val();
				alert(values);
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
			
		//when user clicks button to write review this function is called
		//the course that was selected is passed to the review form
		//which is stored in a hidden input field
		function write_review(){
			var values = $('#hidden_course').val();
			$.ajax({
				url: "rmc_write_review.php",
				type: "GET",
				data: {hidden_course: values},
				success: function(msg){
					$("html").scrollTop(0);
					$("#main_content").html(msg);					
				},
				error:function(){
					$("#main_content").empty().append('something went wrong');
				}
			});
		}
	</script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap-theme.min.css">

    <style>
		.rating {
			float:left;
		}
		/* :not(:checked) is a filter, so that browsers that don’t support :checked don’t 
		   follow these rules. Every browser that supports :checked also supports :not(), so
		   it doesn’t make the test unnecessarily selective */
		.rating:not(:checked) > input {
			position:absolute;
			top:-9999px;
			clip:rect(0,0,0,0);
		}
		.rating:not(:checked) > label {
			float:right;
			width:1em;
			padding:0 .1em;
			overflow:hidden;
			white-space:nowrap;
			cursor:pointer;
			font-size:1.3em;
			line-height:1.2;
			color:#ababab;
			
		}
		.rating:not(:checked) > label:before {
			content: '★ ';
		}
		.rating > input:checked ~ label {
			color: #005A8A;

		}

		#course_details { border-bottom: 1px dashed #ababab; padding-bottom: 5px;}
		#review_header {padding-bottom: 5px; padding-top: 15px;}
		#total_info {border-bottom: 1px dashed #ababab; padding-bottom: 5px; padding-top: 15px;}
		.select_course_list, .select_major_list {width: 50%; padding-bottom: 5px; margin-bottom: 15px;}
		.or {padding-top: 45px;}
		.col-md-4 {padding-left: 0px;}

		header, footer { background:#005A8A; color:#fff; padding: 10px 30px;}
		header a, footer a { color:#fff;}
		footer { background:#333;}
		article { border-bottom: 1px dashed #ababab; padding-bottom: 5px; margin-bottom: 15px;}
		dt,dd {display:inline}
		dd {padding_right: 5px;}

	</style>

</head>
<body>
	<div id="page_body">
	<header>
		<div class="container"><a href=""><img src="rmclogo.png" style="width:155px;height:75px"/></a></div>
	</header>
	<main id="main_content" class="container">

		<h1>RateMyCourse <br>
		<small>Find a course using a method below to view and write reviews</small></h1>
		<div class="col-md-4">
			<!-- user has option to select a category of courses to view -->
			<h3>Category:</h3>
			<select class="form-control" id="choose_category">
				<option selected="selected" value="">---------</option>
				<option value="review_overall">Overall Highest Average Rated</option>
				<option value="review_easiest">Overall Easiest Rated</option>
				<option value="review_mwf">Highest Average Rated Mon/Wed/Fri</option>
				<option value="review_tr">Highest Average Rated Tues/Thurs</option>
				<option value="review_male">Highest Average Rated by Males</option>
				<option value="review_female">Highest Average Rated by Females</option>
				<option value="review_major">Highest Average Rated by Major</option>
			</select>
		</div>
		<div class="col-md-1">
			<h4 class="or">OR</h4>
		</div>
		<div class="col-md-4">
			<!-- user has option to select a specific department -->
			<h3>Department:</h3>
			<select class="form-control" id="choose_dept">
				<option selected="selected" value="">---------</option>
				<?php 	
					$select_dept = mysqli_query($db, "SELECT department_code, department_description FROM department ORDER BY department_description");
					while($row = mysqli_fetch_array($select_dept)){ 
						echo'<option value="'.$row['department_code'].'">'.$row['department_description'].'</option>
						';
					}
				?>
			</select>
		</div>
		<div class="col-md-1">
			<h4 class="or">OR</h4>
		</div>
		<div class="col-md-2">
			<!-- user has option to select a specific subject -->
			<h3>Subject:</h3>
			<select class="form-control" id="choose_subject">
				<option selected="selected" value="">---------</option>
				<?php 	
					$select_course = mysqli_query($db, "SELECT DISTINCT subject FROM course ORDER BY subject");
					while($row = mysqli_fetch_array($select_course)){ 
						echo'<option value="'.$row['subject'].'">'.$row['subject'].'</option>
						';
					}
				?>
			</select>
		</div>
		
		<hr style="clear:both;">		
		
		<!-- courses that are in selected category will be inserted here -->
		<div id="major_list">
		</div>
		
		<!-- courses that are in department or subject will be inserted here -->
		<div id="fill_course">		
		</div>
		
		<!-- reviews for selected course will be inserted here -->
		<div id="fill_reviews">		
		</div>
		
		<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
  
	</main>
    
	<footer><div class="container">&copy; The RMC Team</div></footer>

	</div>
	
</body>
</html>