<?php

namespace App\Console\Commands\Double;

use Illuminate\Console\Command;
use App\Services\DoubleGameService;
use App\Services\TelegramService;

use App\Models\DoubleGame;
use Illuminate\Support\Facades\Http;


class getRolled extends Command
{

    public function __construct(DoubleGameService $doubleGameService, TelegramService $telegramService){
        parent::__construct($this);
        $this->doubleGameService = $doubleGameService;
        $this->telegramService = $telegramService;

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
        $token = config('services.double_game.token');        
        $chatId = config('services.double_game.chatId');
        $messageEnter = 'Proteja:' . "\u{26AA}" . " \n" . 'twitch.tv/link' ." \n" . "<a href='https://www.google.com/'>Ganhe xxx no cadastrando aqui</a>";
        //$this->telegramService->sendMessage($token,$chatId,$messageEnter);
        //dd('3');
        //cores
        //0 => branco
        //1 => vermelho
        //2 => preto

        try {
            //$this->info('buscando dados');
            $values = $this->doubleGameService->getValuesFromAPi();
            if($values){
              //  $this->info('Inserindo Valores');
                $this->doubleGameService->insertBatch($values);
            }

            //$this->info('Pegando os 2 ultimos valores');
            $lastTwoValues = $this->doubleGameService->getLastTwoValues();

            $needSendMessageAnalyzing = $this->doubleGameService->checkIfNeedSendMessageAnalyzing($lastTwoValues);
            if($needSendMessageAnalyzing){
               // $messageId = $this->telegramService->sendMessage($token,$chatId,MESSAGE_ANALYZING);
                $enterTheRound = $this->doubleGameService->checkIfItWillEnterTheRound($lastTwoValues);
                if($enterTheRound['status']){
                    $afterColor = $this->doubleGameService->getAfterColorEnter($enterTheRound['lastValue']['color']);
                    $colorEnter = $this->doubleGameService->getColorEnter($enterTheRound['lastValue']['color']);
                    $messageEnter = 'Após: '. '('.$enterTheRound['lastValue']['roll'].')' . $afterColor ." \n". 'Entrada: '. $colorEnter . " \n" . 'Proteja:' . "\u{26AA}" . " \n" . 'twitch.tv/link' ." \n" . "<a href='https://www.google.com/'>Ganhe xxx no cadastrando aqui</a>";
                   
                    //$this->telegramService->deleteMessage($token,$chatId,$messageId);
                    $this->telegramService->sendMessage($token,$chatId,$messageEnter);
                    $winOrLose = $this->doubleGameService->CheckIfWinOrLose($enterTheRound['lastValue']);
                    if($winOrLose['win']){
                        $this->telegramService->sendMessage($token,$chatId,$winOrLose['message']);

                    }
                    
                    if(!$winOrLose['win']){
                        $this->telegramService->sendMessage($token,$chatId,$winOrLose['message']);
                       /* sleep(3);
                        $message = "\u{1F6A8}". "\u{1F6A8}". "\u{1F6A8}".'Possível momento de recuperação da blaze'."\u{1F6A8}"."\u{1F6A8}"."\u{1F6A8}";
                        $this->telegramService->sendMessage($token,$chatId,$message);
                        sleep(600);*/

                    }
                }

                if(!$enterTheRound['status']){
                    //$this->telegramService->deleteMessage($token,$chatId,$messageId);
                }
            }
            

        } catch (Exception $e) {
            
        }
    }

}
