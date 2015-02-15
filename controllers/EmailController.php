<?php

/*
    Copyright (c) 2015 Zachary Seguin

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

namespace ZacharySeguin\YouWantFood\Controller;

require_once(__DIR__ . '/../vendor/autoload.php');

use \Swift_Message;

class EmailController
{
   private $mailer;
   private $from;

   public function __construct($host, $port, $ssl, $user, $password, $from)
   {
      $transport = null;

      if ($ssl === FALSE)
         $transport = \Swift_SmtpTransport::newInstance($host, $port);
      else
         $transport = \Swift_SmtpTransport::newInstance($host, $port, $ssl);

      $transport->setUsername($user);
      $transport->setPassword($password);

      $this->mailer = \Swift_Mailer::newInstance($transport);
      $this->from = $from;
   }// End of constructor method

   public function sendEmail($email, $subject, $body, $plain = "")
   {
      $message = Swift_Message::newInstance();
      $message->setSubject($subject);
      $message->setFrom($this->from);
      $message->setTo($email);
      $message->setBody($plain);
      $message->addPart($body, "text/html");
      return $this->mailer->send($message);
   }// End of sendEmail function

   public function sendOutletReviewAddedEmail($moderator_email, $outlet_id, $name, $email, $review, $ipaddress, $moderation_token)
   {
      $rejectLink = str_replace("$1", $moderation_token, str_replace("$1", $moderation_token, APP_BASE . "reviews/outlets/reject/$1"));

      $message = Swift_Message::newInstance();
      $message->setSubject("[Outlet Review] Outlet Review Added");
      $message->setFrom($this->from);
      $message->setTo($moderator_email);

      $body = "";
      $body .= "Outlet: $outlet_id\n";
      $body .= "Reviewer: $name ($email)\n";
      $body .= "Submitted From: $ipaddress\n";
      $body .= "Review:\n$review\n";
      $body .= "\n";

      $body .="Reject: $rejectLink\n";

      $message->setBody($body);
      return $this->mailer->send($message);
   }// End of sendReviewAddedEmail function

   public function sendConfirmationEmail($email, $token)
   {
      $message = Swift_Message::newInstance();
      $message->setSubject("You Want Food - Subscription Confirmation");
      $message->setFrom($this->from);
      $message->setTo($email);

      $activationLink = str_replace("$1", $token, APP_BASE . "email/confirm/$1");

      $body = "YOU WANT FOOD - SUBSCRIPTION CONFIRMATION\n";
      $body .= "\n";
      $body .= "Please confirm your email address to receive daily emails from You Want Food.\n";
      $body .= "\n";
      $body .= "Copy and paste the following URL into your browser:\n";
      $body .= $activationLink . "\n";
      $body .= "\n";
      $body .= "If you did not initiate this request or do not want to receive daily emails from You Want Food, please ignore this email.\n";
      $body .= "\n";
      $body .= "THIS IS AN AUTOMATED MESSAGE -- DO NOT REPLY TO IT\n";
      $body .= "If you have any questions or concerns, please email contact@zacharyseguin.ca";

      $message->setBody($body);

      $body = "<h2>You Want Food &mdash; Subscription Confirmation</h2>";
      $body .= "<p>Please confirm your email address to receive daily emails from You Want Food.</p>";
      $body .= "<p><a href='$activationLink'>Confirm your email now.</a></p>";
      $body .= "<p>If the link above does not work, copy and paste the following URL into your browser: $activationLink</p>";
      $body .= "<p style='font-weight: bold;'>If you did not initiate this request or do not want to receive daily emails from You Want Food, please ignore this email.</p>";
      $body .= "<p style='font-size: 0.9em;'>THIS IS AN AUTOMATED MESSAGE -- DO NOT REPLY TO IT<br>If you have any questions or concerns, please email <a href='mailto:contact@zacharyseguin.ca'>contact@zacharyseguin.ca</a>.</p>";

      $message->addPart($body, "text/html");
      return $this->mailer->send($message);
   }// End of sendConfirmationEmail method
}// End of FrontendController class

?>
