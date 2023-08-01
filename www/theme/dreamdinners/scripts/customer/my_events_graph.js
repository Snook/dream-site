const graphConfig = {
	clientID: CLIENT.microsoft.id,
	endpoint: 'https://graph.microsoft.com/v1.0',
	options: {
		redirectUri: PATH.https_server + '/authms_my_events'
	},
	scope: [
		'User.Read',
		'Contacts.Read'
	]
};

async function getGraphContacts(callBack)
{
	const token = await authWithGraph(graphConfig);

	if (token)
	{
		const headers = new Headers();
		const bearer = 'Bearer ' + token;
		headers.append('Authorization', bearer);
		const options = {
			method: 'GET',
			headers: headers
		};
		const graphEndpoint = graphConfig.endpoint + '/me/contacts';

		try
		{
			const response = await fetch(graphEndpoint, options);
			const data = await response.json();

			if (typeof callBack === 'function')
			{
				callBack(data);
			}
			else
			{
				return data;
			}
		}
		catch (err)
		{
			console.error(`There was an error making the request: ${err}`)
		}
	}
	else
	{
		console.error('An auth token must be passed in. To learn more about how to get an auth token for the Microsoft Graph API, check out https://github.com/AzureAD/microsoft-authentication-library-for-js');
	}
}

async function authWithGraph(config)
{
	if (config.clientID && config.scope)
	{
		const userAgentApplication = new Msal.UserAgentApplication(config.clientID, null, null, config.options);
		try
		{
			await userAgentApplication.loginPopup(config.scope);
		}
		catch (error)
		{
			console.error('Error during login', error);
		}

		try
		{
			// Login success
			const accessToken = await userAgentApplication.acquireTokenSilent(config.scope);
			return accessToken;
		}
		catch (error)
		{
			// AcquireTokenSilent Failure, send an interactive request.
			// This will show the Microsoft Account login UI again
			const accessToken = await userAgentApplication.acquireTokenPopup(config.scope);
			return accessToken;
		}
	}
	else
	{
		console.log("You must supply a client id and authentication scopes for your app");
	}
}

$(function () {

	new Msal.UserAgentApplication(CLIENT.microsoft.id, null, null, graphConfig.options);

});