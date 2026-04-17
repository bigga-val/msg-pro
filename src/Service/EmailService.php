<?php

namespace App\Service;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    public function __construct()
    {
    }

    public function sendEmail($to, $subject, $body)
    {
        try {

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $_ENV['MAILER_HOST'] ?? 'mail.msg-pro.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAILER_USERNAME'] ?? 'info@msg-pro.com';
            $mail->Password = $_ENV['MAILER_PASSWORD'];
            $mail->SMTPSecure = 'tls';
            $mail->Timeout = 5;
            $mail->isHTML(true);
            $mail->SetFrom('info@msg-pro.com', 'MSG PRO');
            $mail->addBCC('gabrielkatonge@gmail.com');
            $mail->addBCC('katonge@insoftware.tech');
            $mail->addBCC('gabykatonge@isicom.education');




            $mail->Subject = ucfirst($subject);
            $mail->Body = $body;
            $mail->AddAddress($to);
            return $mail->Send();
        }catch (\Exception $e){
            return false;
        }
    }

    public function confirmerCommandeBody($username, $nbresms){
        $body = "<div style='padding: 10px; text-align: justify;'>";
        $body .="<h4>Cher ". $username;
        $body .=",</h4><p>Merci d'avoir choisi MSG PRO !</p>";
        $body .="<p>Votre commande des ". $nbresms ." SMS a bien été enregistrée.</p>";
        $body .="<p>Notre équipe se charge de préparer votre quota des SMS.";
        $body .="Vous recevrez un email de confirmation avec toutes les informations nécessaires ";
        $body .="dans les prochaines heures. N'hésitez pas à nous contacter si vous avez la moindre question.</p>";
        $body .="<p>Cordialement !</p>";
        $body .="<p>L'équipe commerciale</p>";
        $body .= "</div>";
        return $body;
    }

    public function confirmerCompteBody($username, $email, $confirmationUrl){
        $body = "<h4>Cher(e) ". $username;
        $body .=",</h4><p> Nous sommes ravis de vous accueillir dans la communauté MSG-PRO, la meilleure plateforme des SMS en RDC</p>";
        $body .="<p>Pour activer votre compte et accéder à toutes nos fonctionnalités, veuillez cliquer sur le lien suivant :</p>";
        $body .="<a href='" . $confirmationUrl ."'>Votre lien unique</a>";
        $body .= "<p>Une fois votre compte confirmé, vous pourrez :</p><ul>";
        $body .= "<li>Beneficier des 5 SMS gratuits</li>";
        $body .= "<li>Envoyer des SMS vers differents reseaux de la RDC sans inquietude(Vodacom, Airtel, Orange et Africell)</li>";
        $body .= "<li>Faire des envois rapides des SMS avec un seul identifiant</li>";
        $body .= "<li>Envoyer des SMS en masse à vos groupes des contacts</li>";
        $body .= "<li>Uploader votre fichier des numeros et les enregistrer pour des envois futurs</li>";
        $body .= "</ul><p>N'hésitez pas à nous contacter si vous avez la moindre question.</p>";
        $body .= "<p>Cordialement !</p>";
        $body .= "<p>MSG-PRO</p>";
        $body .= "<p>Telephone: +243 851 331 051</p>";
        return $body;
    }

    public function contactMessageBody($name, $email, $subject, $message){
        $body = "<div style='padding: 10px; font-family: Arial, sans-serif;'>";
        $body .= "<h3>Nouveau message depuis le formulaire de contact</h3>";
        $body .= "<hr>";
        $body .= "<p><strong>Nom :</strong> " . htmlspecialchars($name) . "</p>";
        $body .= "<p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>";
        $body .= "<p><strong>Sujet :</strong> " . htmlspecialchars($subject) . "</p>";
        $body .= "<hr>";
        $body .= "<p><strong>Message :</strong></p>";
        $body .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        $body .= "</div>";
        return $body;
    }

    public function pwdResetBody($username, $pwdID){
        $body = "<h4>Cher(e) ". $username;
        $body .=",</h4><p>Une demande de réinitialisation de mot de passe a été effectuée pour votre compte MSG-PRO</p>";
        $body .="<p>Pour créer un nouveau mot de passe, veuillez cliquer sur le lien suivant :</p>";
        $body .="<a href='https://msg-pro.com/dashboard/resetter?id=" . $pwdID ."'>Votre lien unique</a>";
        $body .= "<p>Une fois sur la page, vous pourrez saisir et confirmer votre nouveau mot de passe.</p>";
        $body .= "<p>Important : </p><ul>";
        $body .= "<li>Ce lien est valide pendant 1 heure(60 minutes). Passé ce délai, vous devrez refaire une demande de réinitialisation.</li>";
        $body .= "<li>Ce lien devient valide une fois que le mot de passe est réinitialisé avec succès.</li>";
        $body .= "<li>Si vous n'êtes pas à l'origine de cette demande, nous vous recommandons de contacter immédiatement notre support client à Info@msg-pro.com</li>";
        $body .= "</ul><p>Merci,</p>";
        $body .= "<p>MSG PRO</p>";
        $body .= "<p>Telephone: +243 851 331 051</p>";
        return $body;
    }
}