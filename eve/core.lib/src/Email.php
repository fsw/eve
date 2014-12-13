<?php
/** 
 * @package Core
 * @author fsw
 */

class Email
{
	public static function obfuscatedLink($email){
		$character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
		$key = str_shuffle($character_set); $cipher_text = ''; $id = 'e'.rand(1,999999999);
		for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])];
		$script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";';
		$script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
		$script.= 'document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';
		$script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; 
		$script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>';
		return '<span id="'.$id.'">[javascript protected email address]</span>'.$script;
	}

	private static function preparehtmlmail($html, $txt, $boundary, $root)
	{
		//searching for images in provided html
		$images = array();
		preg_match_all('~<img.*?src=.([\/.a-z0-9:_-]+).*?>~si',$html,$matches);

		foreach ($matches[1] as $img)
		{
			$img_old = $img;

			if (strpos($img, "http://") === false)
			{
				$uri = parse_url($img);
				$image = array();
				$image['name'] = $uri['path'];
				$image['path'] = Eve::find($root.$uri['path']);
				$content_id = md5($img);
				$html = str_replace($img_old, 'cid:'.$content_id, $html);
				$image['cid'] = $content_id;
				$images[] = $image;
			}
		}

		$nl = "\r\n";

		$headers = 'MIME-Version: 1.0' . $nl;
		$headers .= 'From: no-reply@' . Config::get('site', 'domain', '') . $nl;
		$headers .= 'Content-Type: multipart/related; type="multipart/alternative"; boundary="' . $boundary . '"' . $nl;

		$multipart = '';

		$multipart .= '--' . $boundary . $nl;
		$multipart .= 'Content-Type: multipart/alternative; boundary="_=ALT' . $boundary . '"' . $nl;
		$multipart .= $nl . $nl;

		$multipart .= '--_=ALT' . $boundary . $nl;
		$multipart .= 'Content-Type: text/plain; charset="utf-8"' . $nl;
		$multipart .= 'Content-Transfer-Encoding: quoted-printable' . $nl . $nl;
		$multipart .= $txt . /* chunk_split($txt, 76, '=' . $nl ) .*/ $nl;

		$multipart .= '--_=ALT' . $boundary . $nl;
		$multipart .= 'Content-Type: text/html; charset="utf-8"' . $nl;
		$multipart .= 'Content-Transfer-Encoding: quoted-printable' . $nl . $nl;
		$multipart .= $html . /* chunk_split($html, 76, '=' . $nl ) .*/ $nl;
		$multipart .= '--_=ALT' . $boundary . '--' . $nl . $nl;

		foreach ($images as $path)
		{
			//var_dump($path); die();
			if(file_exists($path['path']))
				$fp = fopen($path['path'],"r");
			if (!@$fp)  {
				return false;
			}

			$imagetype = substr(strrchr($path['path'], '.' ),1);
			$file = fread($fp, filesize($path['path']));
			fclose($fp);

			$message_part = "";

			switch ($imagetype) {
				case 'png':
				case 'PNG':
					$message_part .= "Content-Type: image/png";
					break;
				case 'jpg':
				case 'jpeg':
				case 'JPG':
				case 'JPEG':
					$message_part .= "Content-Type: image/jpeg";
					break;
				case 'gif':
				case 'GIF':
					$message_part .= "Content-Type: image/gif";
					break;
			}

			$message_part .= "; name=\"$path[name]\"$nl";
			$message_part .= "Content-Transfer-Encoding: base64$nl";
			$message_part .= 'Content-ID: <'.$path['cid'].">$nl$nl";
			//$message_part .= 'X-Attachment-Id: '.$path['cid']."$nl$nl";
			//$message_part .= "Content-Disposition: inline; filename=\"".basename($path['path'])."\"\r\n\r\n";
			$message_part .= chunk_split(base64_encode($file)) . $nl;
				
			$multipart .= "--$boundary$nl".$message_part . $nl . $nl;

		}
		$multipart .= "--$boundary--" . $nl;

		return array('multipart' => $multipart, 'headers' => $headers);
	}

	public static function send($to, $subject, $template, $vars = array(), $attach = array())
	{
		$vars['webViewLink'] = Site::lt('webmail', $template, $vars);
		$htmlBody = new Template('emails/' . $template . '/body.html', $vars);
		if (Template::exists('emails/' . $template . '/body.txt'))
		{
			$txtBody = new Template('emails/' . $template . '/body.txt', $vars);
		}
		else
		{
			Eve::requireVendor('markdownify/markdownify.php');
			$md = new Markdownify();
			$txtBody = $md->parseString($htmlBody);
		}

		if ($config = Config::get('mail', 'smtp'))
		{
			Eve::requireVendor('swift/lib/swift_required.php');
			$transport = Swift_SmtpTransport::newInstance($config['host'], $config['port'], $config['encrypt'])
				->setUsername($config['username'])
				->setPassword($config['password'])
				->setAuthMode($config['auth']);
			$mailer = Swift_Mailer::newInstance($transport);
			
			$message = Swift_Message::newInstance($subject)->setFrom($config['username'])->setTo($to);
			//var_dump($message->getHeaders()->toString());
			
			preg_match_all('~<img.*?src=.([\/.a-z0-9:_-]+).*?>~si', $htmlBody, $matches);
			foreach ($matches[1] as $img)
			{
				if (strpos($img, "http://") === false)
				{
					$uri = parse_url($img);
					$path = $uri['path'];
					$path = (strpos($path, '/') === 0) ? $path : ('emails/' . $template . '/' . $path);
					$image = Swift_Image::newInstance(file_get_contents(Eve::find($path)), $uri['path'], 'image/png');
					$cid = $message->embed($image);
					$htmlBody = str_replace($img, $cid, $htmlBody);
				}
			}
			
			$message->addPart($htmlBody /*str_replace('##CID##', $cid, $htmlBody)*/, 'text/html', 'Quoted-Printable');
			$message->addPart($txtBody, 'text/plain', 'Quoted-Printable');
			
			// Send the message
			$result = $mailer->send($message);
			var_dump($result);
			return !empty($result);
			/*$message->setBody($your_plain_text_email_here);
			$message->addPart($your_html_email_here, 'text/html');
			
			var_dump($config); die();*/
			
		}
		else
		{
			$boundary = "=_".md5(uniqid(time()));
			$final_msg = self::preparehtmlmail($htmlBody, $txtBody, $boundary, 'mails/' . $template . '/'); // give a function your html*
			
			return mail(
				is_array($to) ? implode(', ', $to) : $to, 
				$subject, 
				$final_msg['multipart'], 
				$final_msg['headers']);
		}
	}

}
