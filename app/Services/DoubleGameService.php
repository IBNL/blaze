<?php

namespace App\Services;

use App\Models\DoubleGame;
use App\Repositories\DoubleGameRepository;
use App\Services\TelegramService;


use Illuminate\Support\Facades\Http;
use Exception;

class DoubleGameService
{
    protected $doubleGameRepository;

    public function __construct(DoubleGameRepository $doubleGameRepository,TelegramService $telegramService)
    {
        $this->doubleGameRepository = $doubleGameRepository;
        $this->telegramService = $telegramService;

    }
    
    public function getValuesFromAPI(){
        $url = 'https://blaze.com/api/roulette_games/recent';
        $response = Http::get($url);
        if($response->successful()){
            $values = $response->collect();
            return $values;
        }
        return null;
    }

    public function insertBatch($data){
        if($data){
            foreach ($data->sortBy('created_at_blaze') as $item) {
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


    public function checkIfItWillEnterTheRound($lastTwoValues){
        $valueOne = $lastTwoValues[0];
        $valueTwo = $lastTwoValues[1];
        while (true) {
            //fazer requisicao ate pegar a proxima jogada e verificar se vai entrar ou nao
            $values = $this->getValuesFromAPI();
            if($values){
                $lastValue = $values[0];//
                if($lastValue['server_seed'] != $valueOne['server_seed']){
    
                    if($lastValue['color'] == $valueOne['color']){
                        return [ 
                            'status' => true,
                            'lastValue' => $lastValue
                        ];
                    }
                    
                    if($lastValue['color'] != $valueOne['color']){
                        return [ 
                            'status' => false,
                            'color' => null
                        ];
                    }
                }
            }
            sleep(5);
        }
    }

    public function CheckIfWinOrLose($enterTheRoundLastValue){
        $token = config('services.double_game.token');        
        $chatId = config('services.double_game.chatId');
        $gale = 0; 
        $message = '';
        $checkValue = null;
        $galeMessage = '';
        while (true) {
            // perdeu a entrada com a entrada e mais 2 gales
            if($gale >= 3){
                $message = 'Muita Calma.' ."\n". 'Analise a sequência do período e volte mais tarde'. "\n". 'Verifique todas as sequencias do dia em: twitch.tv/link';
                return [
                    'win' => false,
                    'message' => $message
                ]; 
            }

            if($gale == 0){
                $checkValue = $this->getCurrentValue($enterTheRoundLastValue);
            }

            if($gale >= 1){
                $checkValue = $this->getCurrentValue($checkValue);
            }
    
            if($checkValue['color'] == $enterTheRoundLastValue['color']){
                if($gale == 0){
                    //trocar mensagem por uma explicando o gale e a quantidade
                    $galeMessage = 'Calma...'. 'Utilize Martingale no máximo 2 vezes. '."\n\n".
                    'Se entrou com R$ 2'. "\n". 'Entre com R$ 4 no gale1' . "\n\n". 'Se perder novamente '."\n".
                    'Entre com R$ 8 no gale2'. "\n\n". 'Sempre verifique o histórico do dia em twitch.tv/link';
                    $this->telegramService->sendMessage($token,$chatId,$galeMessage);
                }
                /*if($gale == 0){
                    //trocar mensagem por uma explicando o gale e a quantidade
                    $galeMessage = 'Falha na entrada, duplique a aposta para o 1 gale';
                    $this->telegramService->sendMessage($token,$chatId,$galeMessage);
                }*/

                /*if($gale == 1){
                    $galeMessage = 'Falha no 1 gale, duplique a aposta do gale 1 para o 2 gale';
                    $this->telegramService->sendMessage($token,$chatId,$galeMessage);
                }*/
                $gale++;
            }
                
            if($checkValue['color'] != $enterTheRoundLastValue['color']){
                if($gale == 0){
                    $message = "\u{2705}"."\u{1F680}"."\u{1F680}"."\n".'Win na entrada.' . $this->getAfterColorEnter($checkValue['color']);

                }
    
                if($gale == 1){
                    $message = "\u{2705}"."\u{1F680}"."\u{1F680}"."\n".'Win no gale1.' . $this->getAfterColorEnter($checkValue['color']);

                }
        
                if($gale == 2){
                    $message = "\u{2705}"."\u{1F680}"."\u{1F680}"."\n".'Win no gale2.' . $this->getAfterColorEnter($checkValue['color']);

                }
        
                return [
                    'win' => true,
                    'message' => $message
                ]; 
            }
            
        }
    }


    public function getCurrentValue($enterTheRoundLastValue){
        while (true) {
            $values = $this->getValuesFromAPI();
            if($values){
                $this->insertBatch($values);
                $checkValue = $values[0];
                if($checkValue['server_seed'] != $enterTheRoundLastValue['server_seed']){
                    return $checkValue;
                } 
            }

            sleep(5);
        }

    }

    public function getAfterColorEnter($lastColorValue){
        if($lastColorValue == 1){
            return "\u{1F534}";
            ;
        }

        if($lastColorValue == 2){
            return "\u{26AB}";
        }
    }

    public function getColorEnter($lastColorValue){
        if($lastColorValue == 1){
            return "\u{26AB}";
        }

        if($lastColorValue == 2){
            return "\u{1F534}";
        }
    }


    
}