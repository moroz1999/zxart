<?php

use PHPMailer\PHPMailer\PHPMailer;

class EmailDispatchmentSender extends errorLogger
{
    public function sendEmail(
        $content,
        $receiverEmail,
        $fromEmail,
        $fromName,
        $subject,
        $attachmentsList = [],
        $unsubscribeLink = null
    )
    {
        $phpmailerObject = new PHPMailer();

        $phpmailerObject->IsHTML(true);
        $phpmailerObject->CharSet = 'UTF-8';
        $phpmailerObject->WordWrap = 64;
        $phpmailerObject->Encoding = 'base64';

        $configManager = controller::getInstance()->getConfigManager();
        $emailsConfig = $configManager->getConfig('emails');
        $transport = $emailsConfig->get('transport');
        $smtpHost = $emailsConfig->get('smtpHost');
        $smtpPort = $emailsConfig->get('smtpPort');
        $smtpUser = $emailsConfig->get('smtpUser');
        $smtpPass = $emailsConfig->get('smtpPassword');

        switch (strtoupper($transport)) {
            case 'SMTP':
                $phpmailerObject->IsSMTP();
                $phpmailerObject->SMTPAuth = true;
                $phpmailerObject->Host = $smtpHost;
                $phpmailerObject->Port = $smtpPort;
                $phpmailerObject->Username = $smtpUser;
                $phpmailerObject->Password = $smtpPass;
                break;
            default:
            case 'MAIL':
                $phpmailerObject->IsMail();
                break;
        }
        $phpmailerObject->Body = $content;
        $phpmailerObject->AltBody = $this->htmlToPlainText($content);
        $phpmailerObject->AddAddress($receiverEmail);
        $phpmailerObject->From = $fromEmail;
        $phpmailerObject->FromName = $fromName;
        $phpmailerObject->Subject = $subject;

        if ($unsubscribeLink) {
            $phpmailerObject->AddCustomHeader("List-Unsubscribe: " . $unsubscribeLink);
        }

        foreach ($attachmentsList as &$attachmentInfo) {
            if (is_file($attachmentInfo['filePath'])) {
                $phpmailerObject->AddAttachment($attachmentInfo['filePath'], $attachmentInfo['fileName']);
            }
        }

        if ($phpmailerObject->Send()) {
            $result = true;
        } else {
            $this->logError($phpmailerObject->ErrorInfo);
            $result = false;
        }
        return $result;
    }

    protected function htmlToPlainText($src)
    {
        $result = $src;
        $result = html_entity_decode($result, ENT_QUOTES);
        $result = preg_replace('/<style([\s\S]*?)<\/style>/', '', $result); // remove stylesheet
        $result = preg_replace('/[\xA0]*/', '', $result);
        $result = preg_replace('#[\n\r\t]#', "", $result);
        $result = preg_replace('#[\s]+#', " ", $result);
        $result = preg_replace('#(</li>|</div>|</td>|</tr>|<br />|<br/>|<br>)#', "$1\n", $result);
        $result = preg_replace('#(</h1>|</h2>|</h3>|</h4>|</h5>|</p>)#', "$1\n\n", $result);
        $result = strip_tags($result);
        $result = preg_replace('#^ +#m', "", $result); //left trim whitespaces on each line
        $result = preg_replace('#([\n]){2,}#', "\n\n", $result); //limit newlines to 2 max
        $result = trim($result);
        return $result;
    }
}
