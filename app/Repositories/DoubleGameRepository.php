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
                                ->selectRaw('created_at_blaze')
                                ->selectRaw('HOUR(created_at_blaze) as hour_by_created_at_blaze')
                                ->selectRaw('MINUTE(created_at_blaze) as minute_by_created_at_blaze')
                                ->selectRaw("DATE_FORMAT(created_at_blaze, '%i:%s') as minute_and_secods")
                                ->selectRaw('SECOND(created_at_blaze) as second_by_created_at_blaze')
                                ->whereBetween('created_at_blaze',[$initDate,$finalDate])
                                ->groupBy('created_at_blaze')
                                ->orderBy('created_at_blaze','ASC')
                                ->get()->toArray();
        return $resultsByDay; 
    }
    
}
