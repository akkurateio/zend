<?php

/**
 * Subvitamine\Mailjet\Mail is a class that allows you to send emails to an address or addresses of your choice using the MailJet API
 * This class has been created to be used inside a Zend 1 project.
 */

namespace Subvitamine\Mailjet;

use Mailjet\Resources;

class Mail
{
    protected $_mj;
    protected $_translate;

    public function __construct($apiKey, $apiSecret, $translate = null)
    {
        $this->_mj = new \Mailjet\Client($apiKey, $apiSecret);
        $this->_translate = $translate;
    }

    /**
     * Send is the main function of this class, it allows you to send emails.
     * @param  [string] $template    template name to be used, if it is inside a folder, it should be 'folder/template'
     * @param  [object|array] $data  data to be sent to the template, most of its fields are defined by default so the only mandatory field
     * @param  [array] $to is an array of arrays where each item respects the following format ['email' => '...', 'name' => '...']
     * @param  [array]  $attachments [description]
     * @return [object] the response object returned after sending the mail
     */
    public function send($template, $data, $subject, $from, $to, $attachments = [])
    {
        $data = (object) $data;
        $body = [
            'Messages' => [
                [
                    'FromEmail' => $from['email'],
                    'FromName' => $from['name'],
                    'Subject' => $subject,
                    'Text-part' => $this->getText($template, $data),
                    'Html-part' => $this->getHtml($template, $data),
                    'Recipients' => $to,
                    'Attachments' => $attachments,
        ], ], ];
        $response = $this->_mj->post(Resources::$Email, ['body' => $body]);

        return $response;
    }

    /**
     * getText allows you to get text from the template.
     * @param  [string] $template    template name to be used, if it is inside a folder, it should be 'folder/template'
     * @param  [object|array] $data  data to be sent to the template, most of its fields are defined by default so the only mandatory field
     * @return [string] text template
     */
    public function getText($template, $data)
    {
        $view = new \Zend_View;
        $view->setBasePath(PRIVATE_FOLDER.'/mails/templates/');
        $view->data = $data;
        if (! empty($this->_translate)) {
            $view->translate = $this->_translate;
        }

        return $view->render('contents'.DS.$template.'_txt.phtml');
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
        $view->setBasePath(PRIVATE_FOLDER.'/mails/templates/');
        $view->data = $data;
        $view->data->template = $template;
        if (! empty($this->_translate)) {
            $view->translate = $this->_translate;
        }

        return $view->render('structure.phtml');
    }
}
