<?php
if(isset($this->head_css))
{
	foreach ($this->head_css as $css)
	{
		echo '<link href="' . $css . '" rel="stylesheet" type="text/css" />' . "\n";
	}
}
?>
