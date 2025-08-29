Environnements et URLs
Informations concernant nos URLs et environnements.

​
Environnements disponibles
Actuellement, nous disposons uniquement d’un environnement de production. D’autres environnements (staging, développement) pourront être ajoutés ultérieurement en fonction des besoins.
​
Production
API Backend : https://api.lygosapp.com/v1/
​
Accès et Sécurité
L’accès à l’API nécessite une authentification via clé API ou OAuth selon les endpoints.
Toutes les communications passent par HTTPS pour garantir la sécurité des échanges.
Pour accéder aux endpoints de l’environnement ci-dessus, utilisez la base d’URL suivante :

Copy
https://api.lygosapp.com/v1/

List Payment Gateways
GET
/
v1
/
gateway

Try it
Headers
​
api-key
string | nullrequired
Response
200

200
application/json
Successful Response

​
id
string<uuid>required
​
amount
integerrequired
​
currency
stringrequired
​
shop_name
stringrequired
​
user_id
string<uuid>required
​
creation_date
string<date-time>required
​
link
stringrequired
​
message
string | nulldefault:""
​
order_id
string | nulldefault:""
​
success_url
string | nulldefault:""
​
failure_url
string | nulldefault:""

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.lygosapp.com/v1/gateway",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "api-key: <api-key>"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

[
  {
    "id": "3c90c3cc-0d44-4b50-8888-8dd25736052a",
    "amount": 123,
    "currency": "<string>",
    "shop_name": "<string>",
    "message": "<string>",
    "user_id": "3c90c3cc-0d44-4b50-8888-8dd25736052a",
    "creation_date": "2023-11-07T05:31:56Z",
    "link": "<string>",
    "order_id": "<string>",
    "success_url": "<string>",
    "failure_url": "<string>"
  }
]


{
  "detail": {
    "message": "No API Key.",
    "type": "NO_API_KEY_PROVIDED"
  }
}

{
  "detail": {
    "message": "API Key not found",
    "type": "API_KEY_NOT_FOUND"
  }
}

{
  "detail": [
    {
      "loc": [
        "<string>"
      ],
      "msg": "<string>",
      "type": "<string>"
    }
  ]
}
Create Payment Gateway
POST
/
v1
/
gateway

Try it
Headers
​
api-key
string | nullrequired
Body
application/json
​
amount
integerrequired
​
shop_name
stringrequired
​
order_id
stringrequired
​
message
string | nulldefault:""
​
success_url
string | nulldefault:""
​
failure_url
string | nulldefault:""
Response
200

200
application/json
Successful Response

​
id
string<uuid>required
​
amount
integerrequired
​
currency
stringrequired
​
shop_name
stringrequired
​
user_id
string<uuid>required
​
creation_date
string<date-time>required
​
link
stringrequired
​
message
string | nulldefault:""
​
order_id
string | nulldefault:""
​
success_url
string | nulldefault:""
​
failure_url
string | nulldefault:""|

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.lygosapp.com/v1/gateway",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'amount' => 123,
    'shop_name' => '<string>',
    'message' => '<string>',
    'success_url' => '<string>',
    'failure_url' => '<string>',
    'order_id' => '<string>'
  ]),
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "api-key: <api-key>"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
Get Gateway
GET
/
v1
/
gateway
/
{gateway_id}

Try it
Path Parameters
​
gateway_id
string<uuid>required
Response
200

200
application/json
Successful Response

​
id
string<uuid>required
​
amount
integerrequired
​
currency
stringrequired
​
shop_name
stringrequired
​
message
stringrequired
​
user_country
objectrequired
Show child attributes

​
creation_date
string<date-time>required
​
link
stringrequired
​
success_url
stringrequired
​
failure_url
stringrequired
​
order_id
string | nulldefault:""
Create Payment Gateway
Update Gateway
PUT
/
v1
/
gateway
/
{gateway_id}

Try it
Headers
​
api-key
string | nullrequired
Path Parameters
​
gateway_id
string<uuid>required
Body
application/json
​
amount
integer
​
shop_name
string
​
message
string
​
success_url
string
​
failure_url
string
Response
200

200
application/json
Successful Response

The response is of type any.

Get Gateway
Delete Gateway
github
linkedin
Delete Gateway
DELETE
/
v1
/
gateway
/
{gateway_id}

Try it
Headers
​
api-key
string | nullrequired
Path Parameters
​
gateway_id
string<uuid>required
Response
200

200
application/json
Successful Response

The response is of type any.