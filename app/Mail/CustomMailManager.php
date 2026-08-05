<?php

namespace App\Mail;

use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class CustomMailManager extends MailManager
{
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config): EsmtpTransport
    {
        $transport = parent::configureSmtpTransport($transport, $config);

        $stream = $transport->getStream();

        if ($stream instanceof SocketStream && isset($config['stream'])) {
            $stream->setStreamOptions($config['stream']);
        }

        return $transport;
    }
}
