<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Service\EmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(private readonly EmailService $emailService) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $this->emailService->sendEmail(
            $message->getTo(),
            $message->getSubject(),
            $message->getBody(),
        );
    }
}
