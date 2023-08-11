function launch(newURL, newName, newFeatures, orgName) 
{
	var remote = open(newURL, newName, newFeatures);
	
	if (remote.opener == null)
    {
		remote.opener = window;
    }
  
	remote.opener.name = orgName;
  
	return remote;
}

function launchSmall(url) 
{
	launch(url, "DreamDinners", "height=480,width=640,channelmode=0,dependent=0,directories=0,fullscreen=0,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0", "Dream Dinners");
}

function launchLarge(url) 
{
	launch(url, "DreamDinners", "height=600,width=830,channelmode=0,dependent=0,directories=0,fullscreen=0,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0", "Dream Dinners");
}
