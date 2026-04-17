<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\Historique;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SmsService
{
    public function send(string $numero, string $message, string $sender): array
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://api-2.mtarget.fr/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => http_build_query([
                    'username' => $_ENV['SMS_API_USERNAME'],
                    'password' => $_ENV['SMS_API_PASSWORD'],
                    'msisdn'   => $numero,
                    'msg'      => $message,
                    'sender'   => $sender,
                ]),
                CURLOPT_HTTPHEADER     => ['content-type: application/x-www-form-urlencoded'],
            ]);
            $response = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($response, true);
            return $data['results'][0] ?? [];
        } catch (\Exception) {
            return [];
        }
    }

    public function getBalance(): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api-public-2.mtarget.fr/balance',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => 'username=' . $_ENV['SMS_API_USERNAME'] . '&password=' . $_ENV['SMS_API_PASSWORD'],
            CURLOPT_HTTPHEADER     => ['content-type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response);
        return ($data->amount ?? '?') . ' ' . ($data->currency ?? '');
    }

    public function interpolateMessage(string $message, Contact $contact): string
    {
        return str_replace(
            ['[Nom]', '[Postnom]', '[Adresse]', '[Fonction]'],
            [$contact->getNom(), $contact->getPostnom(), $contact->getAdresse(), $contact->getFonction()],
            $message
        );
    }

    public function logHistorique(User $user, string $sender, string $message, string $numero, string $reponse, string $ticket, EntityManagerInterface $em): void
    {
        $this->logHistoriqueOnly($user, $sender, $message, $numero, $reponse, $ticket, $em);
        $em->flush();
    }

    public function logHistoriqueOnly(User $user, string $sender, string $message, string $numero, string $reponse, string $ticket, EntityManagerInterface $em): void
    {
        $historique = new Historique();
        $historique->setSender($sender);
        $historique->setUser($user);
        $historique->setMessage($message);
        $historique->setDate(new \DateTime());
        $historique->setNumero($numero);
        $historique->setReponse($reponse);
        $historique->setTicket($ticket);
        $em->persist($historique);
    }

    public function deductCredit(User $user, EntityManagerInterface $em): void
    {
        $user->setTotalSMS(($user->getTotalSMS() ?? 0) - 1);
        $user->setUsedSMS(($user->getUsedSMS() ?? 0) + 1);
        $em->persist($user);
        $em->flush();
    }
}
