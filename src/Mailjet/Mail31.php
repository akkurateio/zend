<?php

/**
 * Subvitamine\Mailjet\Mail is a class that allows you to send emails to an address or addresses of your choice using the MailJet API
 * This class has been created to be used inside a Zend 1 project.
 */

namespace Subvitamine\Mailjet;

use Mailjet\Resources;

class Mail31
{
    protected $_mj;
    protected $_translate;
    protected $_config;
    protected $_templates_folder;
    protected $_script_folder;
    protected $_contents_folder;
    protected $_template_folder;
    protected $_template_after_contents;
    protected $_partner_folder;

    public function __construct($apiKey, $apiSecret, $translate = null, $config = ['htmlToText' => true])
    {
        $this->_mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $this->_translate = $translate;

        if (! empty($config)) {
            $this->_config = $config;
        }
    }

    /**
     * Send is the main function of this class, it allows you to send emails.
     * @param  [string] $template    template name to be used, if it is inside a folder, it should be 'folder/template'
     * @param  [object|array] $data  data to be sent to the template, most of its fields are defined by default so the only mandatory field
     * @param  [array] $to is an array of arrays where each item respects the following format ['email' => '...', 'name' => '...']
     * @param  [array]  $attachments [description]
     * @return [object] the response object returned after sending the mail
     */
    public function send($template, $data, $subject, $recipients, $attachments = [])
    {
        $data = (object) $data;

        $matches = null;
        preg_match('#.*(?=templates\/scripts\/)#', $template, $matches);
        $this->_partner_folder = $matches[0];

        $matches = null;
        preg_match('#(.+)(\/templates\/)#', $template, $matches);
        $this->_templates_folder = $matches[0];

        $this->_script_folder = $this->_templates_folder.'scripts/';
        $this->_contents_folder = $this->_script_folder.'contents/';

        $matches = null;
        preg_match('#(?<=\/templates\/scripts\/).+#', $template, $matches);
        $this->_template_after_scripts = $matches[0];

        $body = [
            'Messages' => [
                [
                    'From' => $this->getFrom($recipients),
                    'To' => $this->getTo($recipients),
                    //                    'Cc' => $this->getCc($recipients),
                    //                    'Bcc' => $this->getBcc($recipients),
                    'Subject' => $subject,
                    'TextPart' => $this->getText($template, $data),
                    'HtmlPart' => $this->getHtml($template, $data),
                    'Attachments' => $attachments,
                ],
            ],
        ];

        $Cc = $this->getCc($recipients);
        if (! empty($Cc)) {
            $body['Messages'][0]['Cc'] = $Cc;
        }
        $Bcc = $this->getBcc($recipients);
        if (! empty($Bcc)) {
            $body['Messages'][0]['Bcc'] = $Bcc;
        }

        $response = $this->_mj->post(Resources::$Email, ['body' => $body]);

        return $response->getData();
    }

    /**
     * getText allows you to get text from the template.
     * @param  [string] $template    template name to be used, if it is inside a folder, it should be 'folder/template'
     * @param  [object|array] $data  data to be sent to the template, most of its fields are defined by default so the only mandatory field
     * @return [string] text template
     */
    public function getText($template, $data)
    {
        if (! empty($this->_config['htmlToText']) && $this->_config['htmlToText'] == true) {
            $converter = new \voku\Html2Text\Html2Text($this->getHtml($template, $data));

            return $converter->getText();
        } else {
            $view = new \Zend_View;
            $view->data = $data;
            if (! empty($this->_translate)) {
                $view->translate = $this->_translate;
            }

            return $view->render($template.'_txt.phtml');
        }
    }

    /**
     * getText allows you to get html from the template.
     * @param  [string] $template    template name to be used, if it is inside a folder, it should be 'folder/template'
     * @param  [object|array] $data  data to be sent to the template, most of its fields are defined by default so the only mandatory field
     * @return [string] html template
     */
    public function getHtml($template, $data)
    {
        $view = new \Zend_View;
        $view->data = $data;
        $view->data->template = $this->_template_after_scripts;
        $view->data->config = require $this->_partner_folder.'config.php';

        if (! empty($this->_translate)) {
            $view->translate = $this->_translate;
        }

        //        $view->setBasePath($this->_templates_folder);
        try {
            $view->setScriptPath($this->_script_folder);

            return $view->render('structure.phtml');
        } catch (\Exception $exception) {
            error_log(print_r($exception->getMessage(), true));
        }
    }

    /**
     * getTo provides To recipient.
     * @param $recipients
     * @return array|bool
     */
    public function getTo($recipients)
    {
        if (isset($recipients->to) && ! empty($recipients->to)) {
            $to = [
                'Email' => $recipients->to->email,
                'Name' => $recipients->to->fullname,
            ];

            return [$to];
        }

        return false;
    }

    /**
     * getFrom provides From recipient.
     * @param $recipients
     * @return array|bool
     */
    public function getFrom($recipients)
    {
        if (isset($recipients->from) && ! empty($recipients->from)) {
            $from = [
                'Email' => $recipients->from->email,
                'Name' => $recipients->from->fullname,
            ];

            return $from;
        }

        return false;
    }

    /**
     * getCc provides Cc recipient(s).
     * @param $recipients
     * @return array|bool
     */
    public function getCc($recipients)
    {
        if (isset($recipients->cc) && ! empty($recipients->cc)) {
            $ccs = [];
            foreach ($recipients->cc as $cc) {
                $ccs[] = [
                    'Email' => $cc->email,
                    'Name' => $cc->fullname,
                ];
            }

            return $ccs;
        }

        return false;
    }

    /**
     * getBcc provides Bcc recipient(s).
     * @param $recipients
     * @return array|bool
     */
    public function getBcc($recipients)
    {
        if (isset($recipients->cci) && ! empty($recipients->cci)) {
            $bccs = [];
            foreach ($recipients->cci as $bcc) {
                $bccs[] = [
                    'Email' => $bcc->email,
                    'Name' => $bcc->fullname,
                ];
            }

            return $bccs;
        }

        return false;
    }
}
