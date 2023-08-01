
<?php
if(isset($this->head_preload))
{
	foreach ($this->head_preload as $resource)
	{
		echo '	<link ' . ((!empty($resource['preconnect'])) ? 'rel="preconnect"' : 'rel="preload" as="' . $resource['type'] . '"') . ' href="' . $resource['path'] . '" />' . "\n";
	}
}
?>
