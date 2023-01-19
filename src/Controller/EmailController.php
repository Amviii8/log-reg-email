<?php
namespace App\Controller;

use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;

class EmailsController extends AppController
{
    public function sendEmail()
    {
        TransportFactory::setConfig('mailtrap', [
          'host' => 'smtp.mailtrap.io',
          'port' => 2525,
          'username' => 'f0f18ef9038fb9',
          'password' => '17f07a994add97',
          'className' => 'Smtp'
        ]);

        $email = new Email();
        $email->transport('mailtrap');
        $email->from(['you@example.com' => 'Your Name']);
        $email->to('test@example.com');
        $email->subject('Test Email');
        $email->send('This is a test email.');
    }
}
