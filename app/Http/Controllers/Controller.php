<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Lite Pay API",
 *     description="Your API Description",
 *     @OA\Contact(
 *         email="ruthiejay022@gmail.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

/**
 * @OA\Info(title="Lite Pay API", version="1.0.0"),
 * @OA\SecurityScheme(
 *    securityScheme="sanctum",
 *    in="header",
 *    name="bearer",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 *    description="Enter your Bearer token in the format **Bearer {token}**"
 * ),
 */
class Controller // Changed from abstract to concrete class
{
    //
}
