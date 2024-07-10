## WeHeat Proxy

Proxy to use a single call with a refresh token, for use with Home Assistant

### Install

`composer install`

### Get the token and heatpump ID

`php auth.php`

You can request a regular or token without expiration (offline). For HomeAssistant, use offline.

You can get the Heatpump ID from the URL in the portal, or get the data from the API when requested.

### Usage

Configure your webbrowser to point all requests to /public and make your calls to your app, instead of the WeHeat API.
Or call the API path after index.php, eg.

```
GET http://example.com/index.php/api/v1/heat-pumps
```

Your can provide your username/password as Basic Auth, or just the refresh_token as username in basic auth.
You can also set the X-Refresh-Token header.



### Examples

```
/api/v1/heat-pumps
/api/v1/heat-pumps/{heatpumpId}
/api/v1/heat-pumps/{heatpumpId}/logs
/api/v1/heat-pumps/{heatpumpId}/logs/raw
/api/v1/heat-pumps/{heatpumpId}/logs/latest
```

For logs, the following parameters are required in the query string:

startTime (UTC, format yyyy-MM-dd HH:mm:ss)
endTime (UTC, format yyyy-MM-dd HH:mm:ss)
interval (Minute, FiveMinute, FifteenMinute, Hour, Day, Week, Month, Year, with max duration: 2 days, 1 week, 1 month, 1 month, 1 year, 2 years, 5 years, 100 years)