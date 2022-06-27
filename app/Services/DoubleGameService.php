<?php

namespace App\Services;

use App\Models\DoubleGame;
use App\Repositories\DoubleGameRepository;

use Illuminate\Support\Facades\Http;
use Exception;

class DoubleGameService
{
    protected $doubleGameRepository;

    public function __construct(DoubleGameRepository $doubleGameRepository)
    {
        $this->doubleGameRepository = $doubleGameRepository;
    }
    
    public function getValuesFromAPI($url){
        $response = Http::get($url);
        if($response->successful()){
            $values = $response->collect();
            return $values;
        }
        return null;
    }

    public function insertBatch($data){
        foreach ($data->sortBy('created_at') as $item) {
            $doubleGame = DoubleGame::where('id_blaze',$item['id'])->where('server_seed',$item['server_seed'])->first();
            if(!$doubleGame){
                $dataDoubleGameCreate = collect();
                $dataDoubleGameCreate->put('id_blaze',$item['id'])
                                ->put('color', $item['color'])
                                ->put('created_at_blaze', $item['created_at'])
                                ->put('roll', $item['roll'])
                                ->put('server_seed', $item['server_seed']);                       
               $this->create($dataDoubleGameCreate);
            }
       }
    }
    public function create($data){
        try {
            $doubleGame = new DoubleGame();
            $doubleGame->id_blaze = $data['id_blaze'];
            $doubleGame->color = $data['color'];
            $doubleGame->roll = $data['roll'];
            $doubleGame->server_seed = $data['server_seed'];
            $doubleGame->created_at_blaze = $data['created_at_blaze'];
            $doubleGame->save();

            return [
                'code' => 201,
                'model' => $doubleGame
            ];
        } catch (Exception $e) {
            return [
                'code' => 422,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getLastTwoValues(){
        try {
            $lastTwoValues = $this->doubleGameRepository->getLastTwoValues();           
            return $lastTwoValues;
        } catch (Exception $e) {
            return [
                'code' => 422,
                'message' => $e->getMessage()
            ];
        }
    }

    public function checkIfNeedSendMessageAnalyzing($lastTwoValues){
        $valueOne = $lastTwoValues[0];
        $valueTwo = $lastTwoValues[1];
        //se uma das 2 cores for branca nao pode analizar
        if($valueOne['color'] == '0' || $valueTwo['color'] == '0'){
            return false;
        }
        
        // se as 2 cores forem vermelhas
        if($valueOne['color'] == '1' && $valueTwo['color'] == '1'){
            return true;
        }

        //se as 2 cores forem pretas
        if($valueOne['color'] == '2' && $valueTwo['color'] == '2'){
            return true;
        }

        return false;
        
    }

    public function getResultsByDay($requestDay){
        $initDate = $requestDay . ' 00:00';
        $finalDate = $requestDay . ' 23:59';
        $resultsByDay = $this->doubleGameRepository->getResultsByDay($initDate,$finalDate);
        return $resultsByDay;
    }


    
}