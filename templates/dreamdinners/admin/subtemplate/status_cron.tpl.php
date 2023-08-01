<div class="table-responsive">

	<h3>Cron Job Status:  <?php echo CTemplate::dateTimeFormat(date("Y-m-d H:i:s"));?></h3>
	<?php if (!empty($this->statusList)) {
		echo '<table class="table table-striped table-sm">';
		echo "<th>Job</th>";
		echo "<th></th>";
		echo "<th>Status</th>";
		echo "<th>Records Processed</th>";
		echo "<th>Time</th>";

		foreach ($this->statusList as $thisCron => $result)
		{

			echo "<tr><td><span>" . ucfirst(strtolower(str_replace("_", " ", $thisCron)))  . "</span>";
			if (empty($result))
			{
				if ($this->in_progress)
				{
					echo '<td><img src="' . EMAIL_IMAGES_PATH . '/admin/icon/delete.png" /></td>';
					echo "<td>Has not completed. May be in progress or about to run.</td>";
				}
				else
				{
					echo '<td><img src="' . EMAIL_IMAGES_PATH . '/admin/icon/delete.png" /></td>';
					echo "<td>Error: Has not completed.</td>";
				}

				echo "<td>0</td>";
				echo "<td>n/a</td>";

			}
			else
			{
				$noFailures = true;
				$messages = "";
				$recordsProcessed = 0;
				$time = false;
				foreach($result as $id => $data)
				{
					if (!$data['success'])
					{
						$noFailures = false;
						$messages .= "Error: " . $data['comments'] . "<br />";
					}
					else
					{
						$messages .= "Success: " . $data['comments'] . "<br />";
					}

					if ($data['items_processed'] > $recordsProcessed) $recordsProcessed = $data['items_processed'];
					$time = $data['timestamp'];
				}


				if ($noFailures)
				{
					echo '<td><img src="' . EMAIL_IMAGES_PATH . '/admin/icon/accept.png" /></td>';
					echo "<td>$messages </td>";
				}
				else
				{
					echo '<td><img src="' . EMAIL_IMAGES_PATH . '/admin/icon/delete.png" /></td>';
					echo "<td>$messages</td>";
				}

				echo "<td>$recordsProcessed</td>";
				echo "<td>$time</td>";


			}
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "Error: status check returned no results";
	}
	?>


</div>