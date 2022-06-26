<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\DoubleGameService;

class DoubleGameController extends Controller
{
    public function __construct(DoubleGameService $doubleGameService){
        $this->doubleGameService = $doubleGameService;
    }

    public function index(Request $request){
        $requestDay = date('Y-m-d');
        $resultsByDay = $this->doubleGameService->getResultsByDay($requestDay);
        return view('doubleGameIndex',['resultByDay' => $resultsByDay]);
    }

    
}