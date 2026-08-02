<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "L5 Swagger OpenApi description for garage project",
    title: "Laravel Property Management Documentation",
    contact: new OA\Contact(email: "drrifatalashwad0@gmail.com"),
    license: new OA\License(name: "Apache 2.0", url: "http://www.apache.org/licenses/LICENSE-2.0.html")
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "bearerAuth",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer"
)]
#[OA\SecurityScheme(
    securityScheme: "pin",
    type: "apiKey",
    name: "pin",
    in: "header"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Demo API Server"
)]
#[OA\Tag(
    name: "Garages",
    description: "API Endpoints of Garages"
)]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
