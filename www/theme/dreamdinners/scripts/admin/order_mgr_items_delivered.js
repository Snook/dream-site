var itemTabIsDirty = false;

function countSelectedBundleItems()
{
	numBundItems = 0;

	$.each(bundleItemsBundle, function (id, item)
	{

		if ($("#bnd_" + id).is(":checked"))
		{
			numBundItems += parseInt(item.servings_per_item);
		}

	});

	return numBundItems;
}


function setItemTabAsDirty()
{
	itemTabIsDirty = true;
}

function costInput(obj)
{

	setItemTabAsDirty();

	calculateTotal();
}