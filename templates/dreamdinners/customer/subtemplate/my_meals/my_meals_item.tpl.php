<div id="recipe_div-<?php echo $recipe['element_id']; ?>" class="col-12 col-lg-6" xmlns="http://www.w3.org/1999/html">
	<div class="row mb-3">

		<div class="col-md-5 text-center mb-lg-4">
			<img class="img-fluid mb-2 d-block mx-auto" alt="<?php echo htmlspecialchars($recipe['menu_item_name']); ?>" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($recipe['menu_image_override'])) ? 'default' : $recipe['menu_image_override']; ?>/<?php echo $recipe['recipe_id']; ?>.webp">
			<?php if (!empty($recipe['cooking_instruction_youtube_id'])) { ?>
				<div class="d-inline-block">
					<i class="dd-icon icon-video font-size-medium align-middle text-gray-dark"></i>
					<a href="/?page=item&amp;recipe=<?php echo $recipe['recipe_id']; ?>&amp;tab=video" class="font-size-small text-green text-uppercase text-decoration-underline">Preparation video</a>
				</div>
			<?php } ?>
		</div>

		<div class="col-md-7 pl-md-0">
			<div class="row mb-2">
				<div class="col">
					<a class="font-size-medium-small text-uppercase font-weight-bold text-left my-meal-title mb-3" href="/?page=item&amp;recipe=<?php echo $recipe['recipe_id']; ?>"><?php echo htmlspecialchars($recipe['menu_item_name']); ?></a>
				</div>
			</div>
			<div class="row ml-1 mb-3 no-gutters">
				<div class="col-12 col-xl-4 text-center text-md-left">
					<input id="org_rating-<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" type="hidden" value="<?php echo $recipe['rating']; ?>" />
					<ul class="list-inline d-inline-flex font-size-medium">
						<li class="list-inline-item m-0">
							<i class="<?php echo ($recipe['rating'] >= 1) ? 'fas text-yellow' : 'far'; ?> fa-star cursor-pointer my_meals-rate" alt="1 Star" id="rating-1-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-rating="1" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>"></i>
						</li>
						<li class="list-inline-item m-0">
							<i class="<?php echo ($recipe['rating'] >= 2) ? 'fas text-yellow' : 'far'; ?> fa-star cursor-pointer my_meals-rate" alt="2 Star" id="rating-2-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-rating="2" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>"></i>
						</li>
						<li class="list-inline-item m-0">
							<i class="<?php echo ($recipe['rating'] >= 3) ? 'fas text-yellow' : 'far'; ?> fa-star cursor-pointer my_meals-rate" alt="3 Star" id="rating-3-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-rating="3" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>"></i>
						</li>
						<li class="list-inline-item m-0 ">
							<i class="<?php echo ($recipe['rating'] >= 4) ? 'fas text-yellow' : 'far'; ?> fa-star cursor-pointer my_meals-rate" alt="4 Star" id="rating-4-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-rating="4" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>"></i>
						</li>
						<li class="list-inline-item m-0">
							<i class="<?php echo ($recipe['rating'] >= 5) ? 'fas text-yellow' : 'far'; ?> fa-star cursor-pointer my_meals-rate" alt="5 Star" id="rating-5-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-rating="5" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>"></i>
						</li>
					</ul>
				</div>
				<div class="col-12 col-xl-8">
					<div class="input-group input-group-sm justify-content-center justify-content-md-start justify-content-xl-end">
						<span class="input-group-text" id="">Would order again</span>
						<div class="input-group-append">
							<div class="input-group-text">
								<div class="custom-control custom-radio custom-control-inline">
									<input class="custom-control-input my_meals-favorite" type="radio" id="radio_<?php echo $recipe['element_id']; ?>_y" name="radio_<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>" value="y" <?php echo (isset($recipe['favorite']) && $recipe['favorite'] === '1') ? 'checked="checked"' : ''; ?> />
									<label class="custom-control-label cursor-pointer" for="radio_<?php echo $recipe['element_id']; ?>_y">Yes</label>
								</div>
							</div>
							<div class="input-group-text">
								<div class="custom-control custom-radio custom-control-inline">
									<input class="custom-control-input my_meals-favorite" type="radio" id="radio_<?php echo $recipe['element_id']; ?>_n" name="radio_<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>" value="n" <?php echo (isset($recipe['favorite']) && $recipe['favorite'] === '2') ? 'checked="checked"' : ''; ?> />
									<label class="custom-control-label cursor-pointer" for="radio_<?php echo $recipe['element_id']; ?>_n">No</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="font-weight-bold" for="review-<?php echo $recipe['element_id']; ?>">Recipe review</label>
				<textarea class="form-control my_meals-review dd-strip-tags" placeholder="Recipe review" id="review-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>" maxlength="350"><?php echo htmlspecialchars($recipe['comment']); ?></textarea>
				<div class="row mt-2 collapse my_meals-review-submit-row" data-element_id="<?php echo $recipe['element_id']; ?>">
					<div class="col text-center">
						<button class="btn btn-sm btn-primary my_meals-review-submit" data-element_id="<?php echo $recipe['element_id']; ?>" type="button">Submit review</button>
						<button class="btn btn-sm btn-primary my_meals-review-cancel" data-element_id="<?php echo $recipe['element_id']; ?>" type="button">Cancel</button>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="font-weight-bold" for="note-<?php echo $recipe['element_id']; ?>">Personal note</label>
				<textarea class="form-control my_meals-note dd-strip-tags" placeholder="Personal note" id="note-<?php echo $recipe['element_id']; ?>" data-activate_element_id="<?php echo $recipe['element_id']; ?>" data-element_id="<?php echo $recipe['element_id']; ?>" data-recipe_id="<?php echo $recipe['recipe_id']; ?>" data-recipe_version="<?php echo $recipe['recipe_version']; ?>" data-store_id="<?php echo $recipe['store_id']; ?>" maxlength="350"><?php echo htmlspecialchars($recipe['personal_note']); ?></textarea>
				<div class="row mt-2 collapse my_meals-note-submit-row" data-element_id="<?php echo $recipe['element_id']; ?>">
					<div class="col text-center">
						<button class="btn btn-sm btn-primary my_meals-note-submit" data-element_id="<?php echo $recipe['element_id']; ?>" type="button">Save note</button>
						<button class="btn btn-sm btn-primary my_meals-note-cancel" data-element_id="<?php echo $recipe['element_id']; ?>" type="button">Cancel</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>