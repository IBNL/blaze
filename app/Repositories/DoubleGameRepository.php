<?php

namespace App\Repositories;

use App\Models\DoubleGame;

class DoubleGameRepository extends AbstractRepository{

    protected $model = DoubleGame::class;

    public function getLastTwoValues(){
        $lastTwoValues = DoubleGame::orderBy('created_at_blaze','DESC')->limit(2)->get()->toArray();
        return $lastTwoValues;
    }    

    public function getResultsByDay($initDate,$finalDate){
        $resultsByDay = DoubleGame::selectRaw('MAX(color) as number_color')
                                ->selectRaw("(CASE
                                                WHEN MAX(color) = 0 THEN 'yellow'
                                                WHEN MAX(color) = 1 THEN 'red'
                                                WHEN MAX(color) = 2 THEN 'black'
                                            END) as color_name")
                                ->selectRaw('MAX(roll) as roll')
                                ->selectRaw('created_at')
                                ->selectRaw('HOUR(created_at) as hour_by_created_at')
                                ->selectRaw('MINUTE(created_at) as minute_by_created_at')
                                ->selectRaw("DATE_FORMAT(created_at, '%i:%s') as minute_and_secods")
                                ->selectRaw('SECOND(created_at) as second_by_created_at')
                                ->whereBetween('created_at',[$initDate,$finalDate])
                                ->groupBy('created_at','created_at_blaze')
                                ->orderBy('created_at','ASC')
                                ->get()->toArray();
        return $resultsByDay; 
    }
    
}
