<?php

namespace App\Console\Commands\Double;

use Illuminate\Console\Command;
use App\Services\DoubleGameService;
use App\Models\DoubleGame;

class getRolled extends Command
{

    public function __construct(DoubleGameService $doubleGameService){
        parent::__construct($this);
        $this->doubleGameService = $doubleGameService;

    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getRolled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bot com 3 gales';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //cores
        //0 => branco
        //1 => vermelho
        //2 => preto
        define("MESSAGE_ANALYZING", "Analizando");

        try {
            $this->info('buscando dados');
            $values = $this->getValuesFromAPi();
            if($values){
                $this->info('Inserindo Valores');
                $this->doubleGameService->insertBatch($values);
            }

            $this->info('Pegando os 2 ultimos valores');
            $lastTwoValues = $this->getLastTwoValues();
            /*$needSendMessageAnalyzing = $this->doubleGameService->checkIfNeedSendMessageAnalyzing($lastTwoValues);

            if($needSendMessageAnalyzing){
                $this->info(MESSAGE_ANALYZING . ' '.$lastTwoValues[0]['color']);
            }*/
            

        } catch (Exception $e) {
            
        }
    }

    private function getValuesFromAPI(){
        $url = 'https://blaze.com/api/roulette_games/recent';
        $values = $this->doubleGameService->getValuesFromAPI($url);
        if($values){
            return $values;
        }
        return null;

    }

    private function getLastTwoValues(){
        $lastTwoValues = $this->doubleGameService->getLastTwoValues();
        return $lastTwoValues;
    }
}
