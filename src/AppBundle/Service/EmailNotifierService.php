<?php

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;

class EmailNotifierService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var String
     */
    private $fromEmail;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        string $fromEmail,
        LoggerInterface $logger
    )
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->fromEmail = $fromEmail;
        $this->logger = $logger;
    }

    /**
     *  Function to send the Email.
     *
     *  @param $subject
     *  @param $toEmails
     *
     *  @return array
     *  @throws \Exception
     */
    public function sendEmail(string $subject, string $toEmails): array
    {
        $sendEmailResult['status'] = false;
        try {
            $recipients = [];
            $toEmails = explode(',', $toEmails);

            foreach ($toEmails as $email) {
                $recipients[] = trim($email);
            }

            $message = \Swift_Message::newInstance($subject)
                ->setFrom($this->fromEmail)
                ->setTo($recipients)
                ->setBody($this->templating->render("@App/emails/alert.html.twig"), 'text/html')
            ;

            $sentStatus = $this->mailer->send($message);
            if (!$sentStatus) {
                throw new \Exception('Email Could not be sent to recipients');
            }
            $sendEmailResult['status'] = true;
        } catch (\Exception $ex) {
            $this->logger->error("Email couldn't be sent due to => ". $ex->getMessage());
            throw $ex;
        }

        return $sendEmailResult;
    }
}