<p>Thank you for choosing to help test a new Dream Dinners recipe, we are excited to hear your feedback. Please choose which dinner you are testing below to start the brief survey.</p>

<ul class="list-group">
	<?php foreach($this->userTestRecipes as $id => $recipe) { ?>
		<li class="list-group-item"><a href="/?page=my_surveys&amp;survey=<?php echo $recipe['id']; ?>"><?php echo $recipe['title']; ?></a></li>
	<?php } ?>
</ul>