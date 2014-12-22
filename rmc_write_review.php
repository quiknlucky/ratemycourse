<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	
	<?php 
		//connect to the database
		include('database.php');
	?>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script	src="/jquery/dist/jquery.validate.min.js"></script>

	<script>
		
		//on ready function that looks for changes in form controls
		$(document).ready(function() {
			
			//when user uses term select, send term code to rmc_sections to execute query
			//display results in the #section_based_term div
			$('#term_taken').bind('change', function(event){

				var value1 = $(this).val();
				var value2 = $('#hidden_subject').val();
				var value3 = $('#hidden_course').val();
				$.ajax({
					url: "rmc_sections.php",
					type: "GET",
					data: {choose_term: value1, choose_subject: value2, choose_course: value3},
					success: function(msg){
						$("#section_based_term").html(msg);
					},
					error:function(){
						$("#section_based_term").empty().append('something went wrong');
					}
				});
			});
			
			//when the form is submitted it will first be validated
			// then the data will be sent to rmc_save_review
			//form will be replaced by a success or failure message			
			$('#write_review').validate({
				rules: {
					choose_major: "required",
					choose_term: "required",
					choose_section: "required",
					rating: "required",
					review: "required",
					weekly_time: "required",
					difficulty: "required",
					gpa: "required",
					age: "required",
					gender: "required"
				},
				messages: {
					choose_major: "Please enter your major",
					choose_term: "Please select the term you took this course",
					choose_section: "Please select the section you took",
					rating: "Please give this course a rating",
					review: "Please enter a review for this course",
					weekly_time: "Please estimate the weekly time spent on this course",
					difficulty: "Please estimate the difficulty of this course",
					gpa: "Please indicate your GPA range",
					age: "Please indicate your age",
					gender: "Please indicate your gender"
				},
				submitHandler: function(form) {
					var values = $('#write_review').serialize();
					$.post(
						'rmc_save_review.php',
						values,
						function(data){
							$("#main_content").html(data)
						}
					);
					return false;
					
					//TODO: I would prefer to use this format for the ajax call but 
					//		for some reason it does not work, even though I know at
					//		one point it was working
					/* $.ajax({
						url: "rmc_save_review.php",
						type: "POST",
						data: $(this).serialize(),
						success: function(data){
							$("#main_content").html(data);
						},
						error:function(){
							$("#main_content").empty().append('something went wrong');
						}
					}); */
				}
			});		
		});
		
		//if user decides to not submit a review, clicking the cancel button
		//will call this function and the page will reset to the main page
		function cancel_review(){
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
		.rating:not(:checked) > label:hover, .rating:not(:checked) > label:hover ~ label {
			color: #c4a62e;
		}
		fieldset { border:0;}
		.rating > input:checked + label:hover, .rating > input:checked + label:hover ~ label, .rating > input:checked ~ label:hover, .rating > input:checked ~ label:hover ~ label, .rating > label:hover ~ input:checked ~ label {
			color: #c4a62e;
		}
		.rating > label:active {
			position:relative;
			top:2px;
			left:2px;
			dislay:block;
		}
		
		label.error {
			color: red;
			display: block;
			margin: 4px 0 5px 5px;
			padding: 0;
			text-align: left;
}

		header, footer { background:#005A8A; color:#fff; padding: 10px 30px;}
		header a, footer a { color:#fff;}
		footer { background:#333;}

	</style>
	
</head>
<body>
	
    <main id="main_content" class="container">
	
		<?php 
			//put values passed to page in variables
			$sub = $_GET['hidden_course']; 
			
			//value passed in was in the form 'Subject Course_number' (eg 'CSCI 111')
			//need to separate the subject from the course_number in order to run the query 
			$sub_array = explode(' ',$sub);
			$sub_subject = $sub_array[0];
			$sub_course = $sub_array[1];
		?>
		
		<!-- html to display review form -->
		<h1>RateMyCourse <br>
		<small>Fill out the fields below to submit your review for <?=$sub?></small></h1>
		
		<form id="write_review" action="" method="post" class="form-horizontal" novalidate="novalidate">
		
			<!-- hidden fields to store separated subject and course_number values for easier processing -->
			<input hidden disabled="disabled" type="text" id="hidden_subject" value="<?=$sub_subject?>">
			<input hidden disabled="disabled" type="text" id="hidden_course" value="<?=$sub_course?>">
			
			<!-- users must select their major -->
			<h3>Major:</h3>
			<label>
				<select id="stu_major" class="form-control" name="choose_major">
				<option selected="selected" value="">---------</option>
				<?php 	
					$select_major = mysqli_query($db, "SELECT major_code, major_description FROM major ORDER BY major_description");
					while($row = mysqli_fetch_array($select_major)){ 
						echo"<option value='".$row['major_code']."'>".$row['major_description']."</option>
					";
					}
				?>
				</select>
			</label>
			<label for="choose_major" class="error"></label>
			
			<!-- user must select the term they took the selected course -->
			<h3>Term Taken:</h3>
			<label>
				<select id="term_taken" class="form-control" name="choose_term">
				<option selected="selected" value="">---------</option>
				<?php 	
					$select_term = mysqli_query($db, "SELECT term_code, term_description FROM term ORDER BY term_code");
					while($row = mysqli_fetch_array($select_term)){ 
						echo"<option value='".$row['term_code']."'>".$row['term_description']."</option>
					";
					}
				?>
				</select>
			</label>
			<label for="choose_term" class="error"></label>
			<!-- based on the selected term, the corresponding sections offered that term are displayed, the user must select the section they took -->
			<div id="section_based_term">
				
			</div>
			
			<!-- user must select the overall rating for the course from 1 to 5 stars -->
			<fieldset class="rating">
				<h3>Rating</h3>
				<input type="radio" id="star5" name="rating" value="5" />
				<label for="star5" title="Rocks!">5 stars</label>
				<input type="radio" id="star4" name="rating" value="4" />
				<label for="star4" title="Pretty good">4 stars</label>
				<input type="radio" id="star3" name="rating" value="3" />
				<label for="star3" title="Meh">3 stars</label>
				<input type="radio" id="star2" name="rating" value="2" />
				<label for="star2" title="Kinda bad">2 stars</label>
				<input type="radio" id="star1" name="rating" value="1" />
				<label for="star1" title="Sucks big time">1 star</label>
			</fieldset><br />
			<label for="rating" class="error"></label>
	  
			<hr style="clear:both;">
			
			<!-- text field for user to enter long form review, the review table uses an enum for this attribute which are defined in the value -->
			<h3>Review</h3>
			<label>
				<textarea class="form-control" name="review" rows="4" cols="60"></textarea>
			</label>	
			<!-- user must specify how much time was spent on the course per week -->
			<h3>Weekly time:</h3>
			<label class="checkbox-inline">
				<input type="radio" name="weekly_time" value="<1"> Less than 1 hour
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="weekly_time" value="1<3"> 1 to 3 hours
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="weekly_time" value="3<5"> 3 to 5 hours
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="weekly_time" value="5<7"> 5 to 7 hours
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="weekly_time" value=">7"> More than 7 hours
			</label><br />
			<label for="weekly_time" class="error"></label>
			
			<!-- user must enter the perceived difficulty of the course, the review table uses an enum for this attribute which are defined in the value -->
			<h3>Difficulty:</h3>
			<label class="checkbox-inline">
				<input type="radio" name="difficulty" value="Very Easy"> Very Easy
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="difficulty" value="Easy"> Easy
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="difficulty" value="Moderate"> Moderate
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="difficulty" value="Hard"> Hard
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="difficulty" value="Very Hard"> Very Hard
			</label><br />
			<label for="difficulty" class="error"></label>
			
			<!-- user must enter their overall gpa, the review table uses an enum for this attribute which are defined in the value -->
			<h3>GPA:</h3>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="0-1.0"> Below 1.0
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="1.0-1.5"> Between 1.0 and 1.5
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="1.5-2.0"> Between 1.5 and 2.0
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="2.0-2.5"> Between 2.0 and 2.5
			</label>
			<br />
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="2.5-3.0"> Between 2.5 and 3.0
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="3.0-3.5"> Between 3.0 and 3.5
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gpa" value="3.5-4.0"> Between 3.5 and 4.0
			</label><br />
			<label for="gpa" class="error"></label>
			
			<!-- user must select their age -->
			<h3>Age:</h3>
			<label>
				<select id="selected_age" class="form-control" name="age">
					<?php 
						for($i = 13; $i <= 99; $i++){ 
							if ($i == 18){
								echo"<option selected='selected' value='".$i."'>".$i."</option>";
							} else {
								echo"<option value='".$i."'>".$i."</option>";
							}
						}
					?>
				</select>
			</label>
			<label for="age" class="error"></label>			
				
			<!-- user must select their gender, the review table uses an enum for this attribute which are defined in the value -->
			<h3>Gender:</h3>
			<label class="checkbox-inline">
				<input type="radio" name="gender" value="Female"> Female
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gender" value="Male"> Male
			</label>
			<label class="checkbox-inline">
				<input type="radio" name="gender" value="N/A"> N/A
			</label><br />
			<label for="gender" class="error"></label>
			
			<br /><br />
			
			<!-- buttons to either submit the review or cancel -->
			<input type="submit" class="btn btn-primary" value="Submit Review"> 
			<input type="button" class="btn btn-warning" onclick="cancel_review()" value="Cancel">
		</form> 
	  
		<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
      
    </main>
	
</body>
</html>