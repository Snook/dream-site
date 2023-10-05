<?php $this->assign('page_title','Import Base Recipe Items'); ?>
<?php $this->assign('topnav','import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3>Import Base Recipes</h3>

<p>For importing base recipe set. This will overwrite any base recipes in the database with the same id. If a recipe in the database has corrections not yet reflected in the import data, they will be changed to whatever data is in the import.</p>

<h2>Step 1</h2>

<p>Save a tab delimited text file containing the following <span style="color: red;">(Watch out for tab characters in content, there can not be any)</span>:</p>

<ul>
	<li>A - (A) Recipe ID</li>
	<li>B - (B) Recipe name</li>
	<li>C - (V) Ingredients</li>
	<li>D - (T) Packaging</li>
	<li>E - (N) Six Serving Only</li>
	<li>F - (U) Cooking Method</li>
	<li>G - (BC) Cooking time 3 serving</li>
	<li>H - (BI) Cooking time 6 serving</li>
	<li>I - (Q) Grill Icon</li>
	<li>J - (BR) Cooks from Frozen Icon</li>
	<li>K - (R) Under 30 Icon</li>
	<li>L - (S) Heart Icon</li>
	<li>M - (BP) Everyday Dinner</li>
	<li>N - (BQ) Gourmet</li>
	<li>O - (BT) Flavor Profile</li>
	<li>P - (BN) Recipe Expert</li>
	<li>Q - (BV) Cooking Instructions YouTube ID</li>
	<li>R - (W) Allergens</li>
	<li>S - (BE) UPC 3 serving</li>
	<li>T - (BK) UPC 6 serving</li>
	<li>U - (BG) Weight 3 serving</li>
	<li>V - (BM) Weight 6 serving</li>
	<li>W - (BF) Servings per container medium</li>
	<li>X - (BL) Servings per container large</li>
	<li>Y - (BD) Cooking instructions medium</li>
	<li>Z - (BJ) Cooking instructions large</li>
	<li>AA - (C) Menu class</li>
	<li>AB - (AN) Serving size combined</li>
	<li>AC - (Y) Comments</li>
	<li>AD - (X) Serving Size - Itemized</li>
	<li>AE - (Z) Calories</li>
	<li>AF - (AA) Fat</li>
	<li>AG - (AB) Sat Fat</li>
	<li>AH - (AC) Trans Fats</li>
	<li>AI - (AD) Cholesterol</li>
	<li>AJ - (AE) Carbs</li>
	<li>AK - (AF) Fiber</li>
	<li>AL - (AG) Sugars</li>
	<li>AM - (AH) Protein</li>
	<li>AN - (AI) Sodium</li>
	<li>AO - (AJ) % Vit C</li>
	<li>AP - (AK) % Calcium</li>
	<li>AQ - (AL) % Iron</li>
	<li>AR - (AM) % Vit A</li>
</ul>

<h2>Step 2</h2>

<p>Import file:</p>

<form method="post" enctype="multipart/form-data">
<input type="file" name="base_recipe_import" /><br /><br /><input type="submit" class="btn btn-primary btn-sm" value="Import Recipes" />
</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>