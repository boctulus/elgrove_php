<?php 

namespace simplerest\libs;

use PHPMailer\PHPMailer\PHPMailer;

class Utils {
	static function logger($data, $file = 'log.txt'){		
		if (is_array($data) || is_object($data))
			$data = json_encode($data);
		
		return file_put_contents(LOGS_PATH . $file, $data. "\n", FILE_APPEND);
	}

	static function send_mail(string $to_email, string $to_name, $subject, $body, $alt_body = null){
		$config = include CONFIG_PATH . 'config.php';

		$mail = new PHPMailer();
        $mail->isSMTP();
        
        foreach ($config['email']['mailer']['object'] as $k => $prop){
			$mail->{$k} = $prop;
        }	
    
        $mail->setFrom($config['email']['mailer']['from'][0], $config['email']['mailer']['from'][1]);    
        $mail->addAddress($to_email, $to_name);
       
        $mail->Subject = $subject;
		$mail->msgHTML($body); 
		
		if (!is_null($alt_body))
			$mail->AltBody = $alt_body;
		
		//$mail->addAttachment('images/phpmailer_mini.png');       
        

        if (!$mail->send())
        {	
            return $mail->ErrorInfo;
        }else
            return true;
	}
}