<?php
/**
 * Message
 *
 * @copyright Copyright © 2019 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Eadesigndev\PdfGeneratorPro\Model\Email;

use Magento\Framework\Mail\MailMessageInterface;
use Laminas\Mime\Mime;
use Laminas\Mime\PartFactory;
use Laminas\Mail\MessageFactory as MailFactory;
use Laminas\Mime\MessageFactory as MimeFactory;
use Magento\Framework\Mail\Message as MailMessage;

class Message extends MailMessage implements MailMessageInterface
{
    private $partFactory;
    private $mimeMessageFactory;
    protected $zendMessage;
    private $attachments;
    private $messageType = self::TYPE_TEXT;

    //@codingStandardsIgnoreLine
    public function __construct(
        PartFactory $partFactory,
        MimeFactory $mimeMessageFactory,
        $charset = 'utf-8'
    ) {
        $this->partFactory = $partFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->zendMessage = MailFactory::getInstance();
        $this->zendMessage->setEncoding($charset);
    }

    public function createAttachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            $attachmentPart = $this->partFactory->create();
            $attachmentPart->setContent($attachment->getData('file'))
                ->setType(Mime::TYPE_OCTETSTREAM)
                ->setEncoding(Mime::ENCODING_BASE64)
                ->setFileName($attachment->getData('name'))
                ->setDisposition(Mime::DISPOSITION_ATTACHMENT);

            $this->attachments[] = $attachmentPart;
        }

        return $this;
    }

    public function setBody($body)
    {
        $body = self::createMimeFromString($body);
        $attachments = $this->attachments;

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $body->addPart($attachment);
            }
        }

        $this->zendMessage->setBody($body);
        return $this;
    }

    private function createMimeFromString($body)
    {
        $part = $this->partFactory->create();
        $part->setCharset($this->zendMessage->getEncoding());
        $part->setType($this->messageType);
        $part->setContent($body);
        $mimeMessage = $this->mimeMessageFactory->create();
        $mimeMessage->addPart($part);
        return $mimeMessage;
    }

    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    public function setBodyHtml($html)
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    public function setBodyText($text)
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }
}
