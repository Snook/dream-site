<script type="text/javascript">

	function showNotes(date, notes)
	{

		var Notes = document.getElementById('Notes');
		var NotesCell = document.getElementById('NotesCell');

		if (notes != "" && notes != null)
			NotesCell.innerHTML = notes;
		else
			NotesCell.innerHTML = "No Notes";

		Notes.style.display="block"; // display the pop-up

		width = Notes.offsetWidth;
		height = Notes.offsetHeight;

		TeeDee = document.getElementById(date);
		Notes.style.left = (getRealPos(TeeDee,"Left") - (width / 2)) + 'px'; // set the pop-up's left
		Notes.style.top = (getRealPos(TeeDee,"Top")-height) + 'px'; // set the pop-up's top
		Notes.style.zIndex = 2;

	}

	function hideNotes()
	{
		var Notes = document.getElementById('Notes');
		Notes.style.display="none"; // hide the pop-up
	}

		function getRealPos(el,which)
	{
		iPos = 0;
		while (el!=null) {
	 		iPos += el["offset" + which];
			el = el.offsetParent;
		}
		return iPos;
	}

	function showPreview(notes)
	{

		var Notes = document.getElementById('Notes');
		var NotesCell = document.getElementById('NotesCell');
		var button = document.getElementById('preview');

		<?php if (isset($this->notes_preview) && $this->notes_preview !== false) { ?>
			if (notes != null && notes != "")
				NotesCell.innerHTML = notes;
			else
				NotesCell.innerHTML = "No Notes";
		<?php } else { ?>
			NotesCell.innerHTML = "No Notes";
		<?php } ?>

		Notes.style.display="block"; // display the pop-up

		var width = Notes.offsetWidth;
		var height = Notes.offsetHeight;

		Notes.style.left = (getRealPos(button,"Left") - (width / 2)) + 'px'; // set the pop-up's left
		Notes.style.top = (getRealPos(button,"Top")-height) + 'px'; // set the pop-up's top
		Notes.style.zIndex = 2;
	}

	function hidePreview()
	{
		var Notes = document.getElementById('Notes');
		Notes.style.display="none"; // hide the pop-up
	}



</script>

<div id="Notes" class="notesPopup" style="display:none">
	<table cellpadding="0" cellspacing="0"><tr><td>
	<div style="background-color: #d4d0c8"><b><center>&nbsp;Session Notes&nbsp;</center></b></div>
	<table>
	<tr>
		<td id="NotesCell" cellpaddding="10">No Notes</td>
	</tr>
	</table>
	</td></tr></table>
</div>
