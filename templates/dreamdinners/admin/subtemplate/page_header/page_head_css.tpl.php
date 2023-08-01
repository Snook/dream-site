<?php
if(isset($this->head_css))
{
	foreach ($this->head_css as $css)
	{
		echo '<link href="' . $css . '" rel="stylesheet" type="text/css" />' . "\n";
	}
}
if (!empty($this->print_view) && $this->print_view == true)
{
	echo '<link href="' . CSS_PATH . '/admin/print.css?v=' . JAVASCRIPT_CSS_VERSION . '" rel="stylesheet" type="text/css" />' . "\n";
}
?>
