<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitType;

class UnitTypeController extends Controller
{
    public function index(Request $request)
    {

        return response()->json([
            'message' => 'List of unit types',
            'unittypes' => UnitType::all(),
        ]);
    }
}
