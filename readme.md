## WeHeat Proxy

Proxy to use a single call with a refresh token, for use with Home Assistant.

### Archived. Steps to include directly in HomeAssistant

Use either of these secrets. You can generate a refresh token with `php auth.php` and make sure it includes the offline scope. Otherwise use your credentials.

```yaml
weheat_token_payload: "grant_type=refresh_token&client_id=WeheatCommunityAPI&refresh_token=ey...."

weheat_token_payload: "grant_type=password&client_id=WeheatCommunityAPI&scope=openid&username=email@example.com&password=YOURPASSWORD"
```

Now add the sensors to the Rest Platform. Replace the heatpump ID with your own Heatpump ID (from auth.php or as seen in the URL from the portal)

This will fail the first time because it doesn't have a token, but should fix itself after the scan_interval when called again.

```yaml
rest:
  - resource: 'https://auth.weheat.nl/auth/realms/Weheat/protocol/openid-connect/token'
    method: POST
    scan_interval: 280
    headers:
      Content-Type: "application/x-www-form-urlencoded"
      Accept: "application/json"
    payload: !secret weheat_token_payload
    sensor:
        name: WeHeat API Token
        unique_id: weheat_api_token
        value_template: "{% if value_json.access_token | length > 0 %}OK{%else%}FAIL{%endif%}"
        json_attributes:
            - access_token
            - expires_in
            - scope
            - error
            - error_description
  - resource: https://api.weheat.nl/api/v1/heat-pumps/{heatpumpId}/logs/latest
    scan_interval: 60
    headers:
      Accept: application/json
      Content-Type: application/json
      Authorization: "Bearer {{ state_attr('sensor.weheat_api_token', 'access_token') }}"
    sensor:
      - name: WeHeat Heatpump Latest
        unique_id: weheat_heatpump_latest
        value_template: "{{ value_json.state }}"
        json_attributes:
            - heatPumpId
            - timestamp
            - state
            - rpmCtrl
            - error
      - name: WeHeat tWaterIn
        unique_id: weheat_proxy_twaterin
        value_template: "{{ value_json.tWaterIn }}"
        unit_of_measurement: "°C"
      - name: WeHeat tWaterOut
        unique_id: weheat_proxy_twaterout
        value_template: "{{ value_json.tWaterOut }}"
        unit_of_measurement: "°C"    
      - name: WeHeat tAirIn
        unique_id: weheat_proxy_tairin
        value_template: "{{ value_json.tWaterIn }}"
        unit_of_measurement: "°C"
      - name: WeHeat tAirOut
        unique_id: weheat_proxy_tairout
        value_template: "{{ value_json.tAirOut }}"
        unit_of_measurement: "°C"   
      - name: WeHeat t1
        unique_id: weheat_proxy_t1
        value_template: "{{ value_json.t1 }}"
        unit_of_measurement: "°C"  
      - name: WeHeat t2
        unique_id: weheat_proxy_t2
        value_template: "{{ value_json.t2 }}"
        unit_of_measurement: "°C"  
      - name: WeHeat tRoom
        unique_id: weheat_proxy_troom
        value_template: "{{ value_json.tRoom }}"
        unit_of_measurement: "°C"  
      - name: WeHeat tRoomTarget
        unique_id: weheat_proxy_troomtarget
        value_template: "{{ value_json.tRoomTarget }}"
        unit_of_measurement: "°C"  
      - name: WeHeat tThermostatSetpoint
        unique_id: weheat_proxy_tthermostatsetpoint
        value_template: "{{ value_json.tThermostatSetpoint }}"
        unit_of_measurement: "°C"  
      - name: WeHeat cmMassPowerIn
        unique_id: weheat_proxy_cmmasspowerin
        value_template: "{{ value_json.cmMassPowerIn }}"
        unit_of_measurement: "kW"  
      - name: WeHeat cmMassPowerOut
        unique_id: weheat_proxy_cmmasspowerout
        value_template: "{{ value_json.cmMassPowerOut }}"
        unit_of_measurement: "kW"  
```


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

### Fields

status: 40 = Standy, 70 = Heating, 90 = Defrost, 130 = Cooling, 150 = DHW, 160 = Legionella, 170 = Selftest, 180 = ManualControl

### Example HomeAssistant

In secrets.yaml add the refresh token (from auth.php).

```yaml
weheat_refresh_token: "<your token>""
```

In configuration.yaml add a Rest sensor.
```yaml
rest:
  - resource: 'https://<your domain>/api/v1/heat-pumps/{pumpId}/logs/latest'
    scan_interval: 60
    headers:
      content-type: application/json
      X-Refresh-Token: !secret weheat_refresh_token
    sensor:
      - name: WeHeat Heatpump
        unique_id: weheat_proxy_heatpump
        value_template: "{{ value_json.state }}"
        json_attributes:
          - heatPumpId
          - timestamp
          - state
          - rpmCtrl
          - error
      - name: WeHeat tWaterIn
        unique_id: weheat_proxy_twaterin
        value_template: "{{ value_json.tWaterIn }}"
        unit_of_measurement: "°C"
      - name: WeHeat tWaterOut
        unique_id: weheat_proxy_twaterout
        value_template: "{{ value_json.tWaterOut }}"
        unit_of_measurement: "°C"
      - name: WeHeat cmMassPowerIn
        unique_id: weheat_proxy_cmmasspowerin
        value_template: "{{ value_json.cmMassPowerIn }}"
        unit_of_measurement: "kW"  
      - name: WeHeat cmMassPowerOut
        unique_id: weheat_proxy_cmmasspowerout
        value_template: "{{ value_json.cmMassPowerOut }}"
        unit_of_measurement: "kW"  

```
