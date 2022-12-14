<?php

/**
 * Subvitamine\sendSMS\SMS is a class that allows you to send SMS to any number of your choice using the SMSFactor API.
 */

namespace Subvitamine\Smsfactor;

class Sms
{
    protected $SMSFactor_Token;

    public function __construct($SMSFactor_Token)
    {
        $this->SMSFactor_Token = $SMSFactor_Token;
    }

    /**
     * Send is the main function of this class, it allows you to send a SMS.
     * @param  [string] $content    Content of the SMS
     * @param  [array] $numbers  Array of phone number to send the SMS to.
     */
    public function send($content, $numbers)
    {
        $url = 'https://api.smsfactor.com/send/simulate';

        $recipients = [];
        foreach ($numbers as $n) {
            $recipients[] = ['value' => $n];
        }

        $postdata = [
            'sms' => [
                'authentication' => [
                    'token' => $this->SMSFactor_Token,
                ],
                'message' => [
                    'text' => $content,
                ],
                'recipients' => ['gsm' => $recipients],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        $response = curl_exec($ch);
        echo $response;
        curl_close($ch);
    }
}
