<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class TelegramService
{

    public function sendMessage($token,$chatId,$message,$disableWebPagePreview = true,$parseMode = 'html'){
        try {
            $url = "https://api.telegram.org/bot".$token."/sendMessage";
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'disable_web_page_preview' => $disableWebPagePreview,
                'parse_mode' => $parseMode

            ]);
            
            if($response->successful()){
                $responeData = $response->collect();
                $messageId = $responeData['result']['message_id'];
                return $messageId;
            }

        } catch (Exception $e) {

        }
    }

    public function deleteMessage($token,$chatId,$messageId){        
        $url = "https://api.telegram.org/bot".$token."/deleteMessage";  
        $response = Http::post($url, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }
}