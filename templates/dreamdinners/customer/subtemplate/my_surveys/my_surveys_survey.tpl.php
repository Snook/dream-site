<p>Thank you for taking the time to complete this survey. We will use this information to provide dinners that meet your needs. The following questions will help us develop great dinners. We truly appreciate your input.</p>

<p>Please be sure you have prepared and eaten the dinner before continuing with the survey.</p>

<div class="border-bottom border-green-dark mb-3 mx-5"></div>

<form action="/main.php?page=my_surveys" method="post" class="needs-validation" novalidate>

	<input type="hidden" name="survey_id" value="<?php echo $this->recipe['id']; ?>" />

	<div class="row mb-4">
		<div class="col">
			<p>How do you rate <?php echo $this->recipe['title']; ?> on a scale of 1 to 5, with 1 being Extremely Dissatisfied and 5 being Extremely Satisfied.</p>

			<div class="row mx-0 py-2">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>Ease of preparation</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-1" name="ease_of_prep" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-ease_of_prep-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-2" name="ease_of_prep" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-ease_of_prep-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-3" name="ease_of_prep" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-ease_of_prep-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-4" name="ease_of_prep" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-ease_of_prep-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-5" name="ease_of_prep" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-ease_of_prep-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-ease_of_prep-0" name="ease_of_prep" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-ease_of_prep-0">N/A</label>
					</div>
				</div>
			</div>

			<div class="row mx-0 py-2 bg-gray-light border-top border-bottom">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>Did it look appealing after preparation?</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-1" name="look_appealing" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-look_appealing-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-2" name="look_appealing" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-look_appealing-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-3" name="look_appealing" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-look_appealing-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-4" name="look_appealing" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-look_appealing-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-5" name="look_appealing" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-look_appealing-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-look_appealing-0" name="look_appealing" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-look_appealing-0">N/A</label>
					</div>
				</div>
			</div>

			<div class="row mx-0 py-2">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>I liked the taste</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-1" name="i_liked_taste" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-i_liked_taste-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-2" name="i_liked_taste" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-i_liked_taste-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-3" name="i_liked_taste" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-i_liked_taste-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-4" name="i_liked_taste" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-i_liked_taste-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-5" name="i_liked_taste" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-i_liked_taste-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-i_liked_taste-0" name="i_liked_taste" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-i_liked_taste-0">N/A</label>
					</div>
				</div>
			</div>

			<div class="row mx-0 py-2 bg-gray-light border-top border-bottom">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>My family liked the taste</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-1" name="family_liked_taste" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-family_liked_taste-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-2" name="family_liked_taste" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-family_liked_taste-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-3" name="family_liked_taste" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-family_liked_taste-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-4" name="family_liked_taste" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-family_liked_taste-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-5" name="family_liked_taste" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-family_liked_taste-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-family_liked_taste-0" name="family_liked_taste" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-family_liked_taste-0">N/A</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>How do you rate <?php echo $this->recipe['title']; ?> on a scale of 1 to 5, with 1 being "Not at all" and 5 being "Far too much".</p>

			<div class="row mx-0 py-2">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>Salty Taste</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-1" name="salty_taste" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-salty_taste-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-2" name="salty_taste" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-salty_taste-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-3" name="salty_taste" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-salty_taste-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-4" name="salty_taste" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-salty_taste-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-5" name="salty_taste" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-salty_taste-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-salty_taste-0" name="salty_taste" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-salty_taste-0">N/A</label>
					</div>
				</div>
			</div>

			<div class="row mx-0 py-2 bg-gray-light border-top border-bottom">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>Spicy Taste</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-1" name="spicy_taste" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-spicy_taste-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-2" name="spicy_taste" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-spicy_taste-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-3" name="spicy_taste" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-spicy_taste-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-4" name="spicy_taste" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-spicy_taste-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-5" name="spicy_taste" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-spicy_taste-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-spicy_taste-0" name="spicy_taste" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-spicy_taste-0">N/A</label>
					</div>
				</div>
			</div>

			<div class="row mx-0 py-2">
				<div class="col-12 col-md-2 text-center text-md-left">
					<p>Kid Friendly</p>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-1" name="kid_friendly" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-kid_friendly-1">One</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-2" name="kid_friendly" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-kid_friendly-2">Two</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-3" name="kid_friendly" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-kid_friendly-3">Three</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-4" name="kid_friendly" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-kid_friendly-4">Four</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-5" name="kid_friendly" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-kid_friendly-5">Five</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-kid_friendly-0" name="kid_friendly" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-kid_friendly-0">N/A</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>Would you like to see <?php echo $this->recipe['title']; ?> on the menu?</p>

			<div class="row">
				<div class="col col-md-2">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-would_like_on_menu-1" name="would_like_on_menu" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-would_like_on_menu-1">Yes</label>
					</div>
				</div>
				<div class="col col-md-2">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-would_like_on_menu-0" name="would_like_on_menu" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-would_like_on_menu-0">No</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>Knowing that you can customize your dinners, would you order <?php echo $this->recipe['title']; ?> from our menu?</p>

			<div class="row">
				<div class="col col-md-2">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-order_as_is-1" name="order_as_is" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-order_as_is-1">Yes</label>
					</div>
				</div>
				<div class="col col-md-2">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-order_as_is-0" name="order_as_is" type="radio" value="0" required/>
						<label class="custom-control-label" for="question-order_as_is-0">No</label>
					</div>
				</div>

				<div id="order_as_is_yes" class="col-12 mt-2 collapse">
					<div class="row p-2 mx-0 bg-gray-light">
						<div class="col-12">
							<p>Please indicate how would you use this meal. Check all that apply.</p>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_dinner_week" name="order_as_is_yes_dinner_week" type="checkbox" value="Week dinner" />
								<label class="custom-control-label" for="order_as_is_yes_dinner_week"> Dinner during the week</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_dinner_weekend" name="order_as_is_yes_dinner_weekend" type="checkbox" value="Weekend dinner" />
								<label class="custom-control-label" for="order_as_is_yes_dinner_weekend"> Weekend dinner</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_entertaining" name="order_as_is_yes_entertaining" type="checkbox" value="Entertaining" />
								<label class="custom-control-label" for="order_as_is_yes_entertaining"> Entertaining for guests</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_on_the_go" name="order_as_is_yes_on_the_go" type="checkbox" value="On the go" />
								<label class="custom-control-label" for="order_as_is_yes_on_the_go"> Take on the go</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_lunch" name="order_as_is_yes_lunch" type="checkbox" value="Lunch" />
								<label class="custom-control-label" for="order_as_is_yes_lunch"> Easy Lunch option</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_share" name="order_as_is_yes_share" type="checkbox" value="Share with friend" />
								<label class="custom-control-label" for="order_as_is_yes_share"> Share with a friend</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_yes_other" name="order_as_is_yes_other" type="checkbox" value="Other" />
								<label class="custom-control-label" for="order_as_is_yes_other"> Other (please explain below)</label>
							</div>
						</div>
					</div>
				</div>

				<div id="order_as_is_no" class="col-12 mt-2 collapse">
					<div class="row p-2 mx-0 bg-gray-light">
						<div class="col-12">
							<p>Please indicate why you would not order this. Check all that apply.</p>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_appearance" name="order_as_is_no_appearance" type="checkbox" value="Appearance" />
								<label class="custom-control-label" for="order_as_is_no_appearance"> Appearance</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_aroma" name="order_as_is_no_aroma" type="checkbox" value="Aroma" />
								<label class="custom-control-label" for="order_as_is_no_aroma"> Aroma</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_taste" name="order_as_is_no_taste" type="checkbox" value="Taste" />
								<label class="custom-control-label" for="order_as_is_no_taste"> Taste</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_nutrition" name="order_as_is_no_nutrition" type="checkbox" value="Nutrition" />
								<label class="custom-control-label" for="order_as_is_no_nutrition"> Nutrition</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_ingredient" name="order_as_is_no_ingredient" type="checkbox" value="Ingredient" />
								<label class="custom-control-label" for="order_as_is_no_ingredient"> Specific ingredient (please explain below)</label>
							</div>
						</div>
						<div class="col">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input class="custom-control-input" id="order_as_is_no_other" name="order_as_is_no_other" type="checkbox" value="Other" />
								<label class="custom-control-label" for="order_as_is_no_other"> Other (please explain below)</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>Overall, how satisfied were you with <?php echo $this->recipe['title']; ?>?</p>

			<div class="row">
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-overall_satisfaction-1" name="overall_satisfaction" type="radio" value="1" required/>
						<label class="custom-control-label" for="question-overall_satisfaction-1">Extremely Dissatisfied</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-overall_satisfaction-2" name="overall_satisfaction" type="radio" value="2" required/>
						<label class="custom-control-label" for="question-overall_satisfaction-2">Somewhat Dissatisfied</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-overall_satisfaction-3" name="overall_satisfaction" type="radio" value="3" required/>
						<label class="custom-control-label" for="question-overall_satisfaction-3">Neither Satisfied nor Dissatisfied</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-overall_satisfaction-4" name="overall_satisfaction" type="radio" value="4" required/>
						<label class="custom-control-label" for="question-overall_satisfaction-4">Somewhat Satisfied</label>
					</div>
				</div>
				<div class="col">
					<div class="custom-control custom-radio custom-control-inline">
						<input class="custom-control-input" id="question-overall_satisfaction-5" name="overall_satisfaction" type="radio" value="5" required/>
						<label class="custom-control-label" for="question-overall_satisfaction-5">Extremely Satisfied</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>What do you like BEST about <?php echo $this->recipe['title']; ?>?</p>

			<textarea class="w-100" id="question-liked_best" name="liked_best" required></textarea>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col">
			<p>Any suggestions for improvements that could make <?php echo $this->recipe['title']; ?> better?</p>

			<textarea class="w-100" id="question-suggest_improvements" name="suggest_improvements" required></textarea>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<?php if (!empty($this->review_survey)) { ?>
				<span class="btn btn-primary disabled" data-tooltip="The survey is in Home Office question review mode">(Disabled) Home Office review mode</span>
			<?php } else { ?>
				<input type="submit" id="submit_my_survey" name="submit_my_survey" class="btn btn-primary btn-block btn-spinner" value="Submit Feedback" />
				<div class="invalid-feedback form-feedback text-center">Please complete all questions, thank you.</div>
			<?php } ?>
		</div>
	</div>

</form>