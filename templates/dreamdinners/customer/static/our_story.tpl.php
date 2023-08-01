<?php $this->assign('page_title', 'About Us | Our Story');?>
<?php $this->assign('page_description','Dream Dinners is about getting families around the dinner table with a delicious home cooked meal.'); ?>
<?php $this->assign('page_keywords','family dinner, dinner around the table, dinner preparation, meal preparation, homemade dinner, fix and freeze, freezer dinner'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/main.php?static=about_us" class="btn btn-primary"><span class="pr-2">&#10094;</span> About Us</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Our Story</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<div class="container">
			<div class="row mb-4">
				<div class="col">
					<p class="text-center"><img src="<?php echo IMAGES_PATH; ?>/about_us/family-dinner-mom-son-summer.jpg" alt="Dream Dinners Story" class="img-fluid" /></p>
					<p>"What's for dinner?" should not have to be the first thought you have when your feet touch the floor in the morning. At Dream Dinners, we want you to feel prepared for your day knowing you and your family will be well nourished and able to focus on who's around the dinner table instead of what's on it. Dream Dinners strives to grow great kids and strong relationships by allowing families more time to connect over a well-balanced meal. In today's world more than ever, time around the table as a family is incredibly important. It has proven to reduce children's truancy and depression rates, as well as lead to improved grades and academic school performance. With time being a rare and precious resource for modern families, Dream Dinners can help alleviate the dinnertime stress and help families focus on what really matters.</p>
					<p class="text-center"><img src="<?php echo IMAGES_PATH; ?>/about_us/mill-creek-store-interior-1400x400.jpg" alt="Inside Dream Dinners" class="img-fluid" /></p>
					<p>Dream Dinners has been living its mission of growing great kids and strong families long before its origin in 2002. The co-founder, Stephanie Allen, began creating fix-and-freeze meals as a solution to her family's busy lifestyle since 1986. As she began helping and coaching her friends create their own meals, Stephanie enlisted the help and expertise of Tina Kuna, a long-time friend and experienced business manager, to create a business strategy that would help working mothers like themselves have access to fresh and healthy meals. Together, Stephanie and Tina rose to action and began streamlining the process of taking raw fresh ingredients and creating healthy freeze ahead meals that were tasty and easy to make at home.	</p>
					<p>Three months after hosting their first large-scale meal assembly session, the partners opened up their first Dream Dinners store in Everett, Washington. Within six months, the company opened up two more Washington-based locations and began receiving nationwide recognition and interest. By early 2003, Dream Dinners opened its model to expansion and received more than 6,800 applications from potential franchise owners wanting to bring the Dream Dinners lifestyle into their own communities. </p>
					<p>Many other meal assembly companies have come and gone since Dream Dinners first opened their doors, but it is Dream Dinners' core value of bringing families back around the dinner table that has set the company apart from all others and is the foundation of its success and longevity. </p>
				</div>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>