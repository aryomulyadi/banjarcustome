<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class NotFoundController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('errors.404', [], 404);
    }
}
