<?php

namespace App\MessageHandler;

use App\Message\SendContactEmailMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsMessageHandler]
final class SendContactEmailMessageHandler
{
    private LoggerInterface $logger;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer, Environment $twig)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function __invoke(SendContactEmailMessage $message): void
    {
        try {

            $emailContent = $this->twig->render('emails/contact.html.twig', [
                'data' => [
                    'firstname' => $message->getFirstname(),
                    'lastname' => $message->getLastname(),
                    'email' => $message->getEmail(),
                    'content' => $message->getContent(),
                ],
            ]);

            $email = (new Email())
                ->from('hello@justinmassart.dev')
                ->to($message->getEmail())
                ->subject('Contact | Recipy')
                ->html($emailContent);

            $this->mailer->send($email);
        } catch (TransportExceptionInterface|LoaderError|RuntimeError|SyntaxError $e) {
            $this->logger->error('Failed to send email: '.$e->getMessage(), ['exception' => $e]);
        }
    }
}
