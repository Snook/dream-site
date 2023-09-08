<div class="row mb-4">

	<div class="col-md-3">
		<a href="/item?recipe=<?php echo $mainItem['recipe_id']; ?>">
			<img src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($mainItem['menu_image_override'])) ? 'default' : $mainItem['menu_image_override']; ?>/<?php echo $mainItem['recipe_id']; ?>.webp" alt="<?php echo htmlspecialchars($mainItem['display_title']); ?>" />
			<?php //if ( && $mainItem['out_of_stock']) echo'<span class="out_of_stock"></span>'; else if ($mainItem['limited_qtys'])  echo'<span class="limited_qtys"></span>'; ?>
		</a>
	</div>

	<div class="col-md-9" id="description-<?php echo $mainItem['recipe_id']; ?>">

		<h4><a href="/item?recipe=<?php echo $mainItem['recipe_id']; ?>"><?php echo htmlspecialchars($mainItem['display_title']); ?></a></h4>

		<p><?php echo $mainItem['display_description']; ?></p>

		<div>
			<table>
				<tr>
					<td colspan="6"><span class="font-weight-bold">Overall Rating</span></td>
				</tr>
				<tr>
					<td class="rating_td"><input value="1" type="radio" id="rating-1-<?php echo $mainItem['element_id']; ?>" name="rating-<?php echo $mainItem['id']; ?>" data-element_id="<?php echo $mainItem['element_id']; ?>" data-rating="1" data-rated="<?php echo $mainItem['rating']; ?>" data-menu_id="<?php echo $mainItem['menu_id']; ?>" data-recipe_id="<?php echo $mainItem['recipe_id']; ?>" data-recipe_version="<?php echo $mainItem['recipe_version']; ?>" /><br />Didn&rsquo;t Like</td>
					<td class="rating_td"><input value="2" type="radio" id="rating-2-<?php echo $mainItem['element_id']; ?>" name="rating-<?php echo $mainItem['id']; ?>" data-element_id="<?php echo $mainItem['element_id']; ?>" data-rating="2" data-rated="<?php echo $mainItem['rating']; ?>" data-menu_id="<?php echo $mainItem['menu_id']; ?>" data-recipe_id="<?php echo $mainItem['recipe_id']; ?>" data-recipe_version="<?php echo $mainItem['recipe_version']; ?>" /><br />2</td>
					<td class="rating_td"><input value="3" type="radio" id="rating-3-<?php echo $mainItem['element_id']; ?>" name="rating-<?php echo $mainItem['id']; ?>" data-element_id="<?php echo $mainItem['element_id']; ?>" data-rating="3" data-rated="<?php echo $mainItem['rating']; ?>" data-menu_id="<?php echo $mainItem['menu_id']; ?>" data-recipe_id="<?php echo $mainItem['recipe_id']; ?>" data-recipe_version="<?php echo $mainItem['recipe_version']; ?>" /><br />3</td>
					<td class="rating_td"><input value="4" type="radio" id="rating-4-<?php echo $mainItem['element_id']; ?>" name="rating-<?php echo $mainItem['id']; ?>" data-element_id="<?php echo $mainItem['element_id']; ?>" data-rating="4" data-rated="<?php echo $mainItem['rating']; ?>" data-menu_id="<?php echo $mainItem['menu_id']; ?>" data-recipe_id="<?php echo $mainItem['recipe_id']; ?>" data-recipe_version="<?php echo $mainItem['recipe_version']; ?>" /><br />4</td>
					<td class="rating_td"><input value="5" type="radio" id="rating-5-<?php echo $mainItem['element_id']; ?>" name="rating-<?php echo $mainItem['id']; ?>" data-element_id="<?php echo $mainItem['element_id']; ?>" data-rating="5" data-rated="<?php echo $mainItem['rating']; ?>" data-menu_id="<?php echo $mainItem['menu_id']; ?>" data-recipe_id="<?php echo $mainItem['recipe_id']; ?>" data-recipe_version="<?php echo $mainItem['recipe_version']; ?>" /><br />Loved</td>
				</tr>
			</table>

			<table>
				<tr>
					<td>
						<div class="font-weight-bold">Would you order this again?</div>
						<span><input data-our_checked="0" onclick=" againBtnClick(this, event);" value="1" type="radio" id="fag_<?php echo $mainItem['id']?>" name="fag_<?php echo $mainItem['id']?>">Yes</span>
						<span><input data-our_checked="0" onclick=" againBtnClick(this, event);" value="0" type="radio" id="fag_<?php echo $mainItem['id']?>" name="fag_<?php echo $mainItem['id']?>">No</span>
					</td>
				</tr>
			</table>

			<?php if (CUser::isLoggedIn()) { // Show only if logged in per marketing [CES 07-20-2012] ?>
				<table>
					<tr>
						<td>
							<div>Mark as Favorite?</div>
							<span><input data-favorite="0" data-recipe_id="<?php echo $mainItem['recipe_id']?>" data-element_id="<?php echo $mainItem['element_id']?>" data-recipe_version="<?php echo $mainItem['recipe_version']?>" data-menu_id="<?php echo $mainItem['menu_id']?>" type="checkbox" id="favorite-<?php echo $mainItem['element_id']?>" name="favorite-<?php echo $mainItem['element_id']?>">Yes</span>
						</td>
					</tr>
				</table>
			<?php } ?>
			<table>
				<tr>
					<td>
						<div class="font-weight-bold">Comments</div>
						<textarea name="comment_<?php echo $mainItem['id']?>" maxlength="350" cols="50" style="width:535px;height:100px;"></textarea>
					</td>
				</tr>
			</table>
		</div>

	</div>

</div>