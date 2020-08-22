<?php

namespace PhalApi\XMail;

use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;

/**
 * 邮件工具类.
 *
 * - 基于PHP-IMAP的邮件接收
 *
 */
class Lite
{
    protected $debug;
    protected $instance;

    public function __construct($debug = false)
    {
        $di = \PhalApi\DI();
        $this->debug = $debug;
        $cfg = $di->config->get('app.XMail.email');
        try {
            $mailbox = new Mailbox(
                '{'.$cfg['host'].':'.$cfg['port'].'/'.$cfg['protocol'].'/'.$cfg['secure'].'}', // IMAP server
                $cfg['username'], // Username for the before configured mailbox
                $cfg['password'], // Password for the before configured username
                null, // Directory, where attachments will be saved (optional)
                'UTF-8' // Server encoding (optional)
            );
            $mailbox->setAttachmentsIgnore(true);
            $this->instance = $mailbox;
        } catch (ConnectionException $ex) {
            $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['IMAP connection failed' => $ex->getMessage()]);
        } catch (Exception $ex) {
            $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['error' => $ex->getMessage()]);
        }
    }

    public function __destruct()
    {
        if(!isset($this->instance)) return;
        $this->instance->disconnect();
    }

    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * 获取邮件
     *
     * @param string $criteria 类型
     *
     * @return array list
     */
    public function getMails($criteria = 'ALL')
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $mail_ids = $this->instance->searchMailbox($criteria);
        if ($this->debug) {
            $di->logger->debug(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['data' => $mail_ids]);
        }
        $mails = [];
        foreach ($mail_ids as $mail_id) {
            $email = $this->instance->getMail(
                $mail_id, // ID of the email, you want to get
                false // Do NOT mark emails as seen (optional)
            );
            array_push($mails, [
                'fromName' => (string) (isset($email->fromName) ? $email->fromName : $email->fromAddress),
                'fromEmail' => (string) $email->fromAddress,
                'to' => (string) $email->toString,
                'subject' => (string) $email->subject,
                'messageId' => (string) $email->messageId,
                'hasAttachments' => $email->hasAttachments(),
                'content' => $email->textHtml ? $email->textHtml : $email->textPlain,
            ]);
        }
        return $mails;
    }

    /**
     * 获取邮件列表信息
     *
     * @param string $action 动作
     * @param string $criteria 类型
     *
     * @return array list
     */
    public function getMailsInfo($criteria = 'ALL', $action = 'SORT')
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $mail_ids = [];
        if($action === 'SORT') {
            $mail_ids = $this->getSortMailsIds($criteria);
        } else {
            $mail_ids = $this->instance->searchMailbox($criteria);
        }
        if ($this->debug) {
            $di->logger->debug(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['data' => $mail_ids]);
        }
        $mails = $this->instance->getMailsInfo($mail_ids);
        return $mails;
    }

    /**
     * 获取邮件排序后ID列表
     *
     * Criteria can be one (and only one) of the following constants:
     *  SORTDATE - mail Date
     *  SORTARRIVAL - arrival date (default)
     *  SORTFROM - mailbox in first From address
     *  SORTSUBJECT - mail subject
     *  SORTTO - mailbox in first To address
     *  SORTCC - mailbox in first cc address
     *  SORTSIZE - size of mail in octets
     *
     * @param int           $criteria       Sorting criteria (eg. SORTARRIVAL)
     * @param bool          $reverse        Sort reverse or not
     * @param string|null   $searchCriteria See http://php.net/imap_search for a complete list of available criteria
     *
     * @psalm-param value-of<Imap::SORT_CRITERIA> $criteria
     * @psalm-param 1|5|0|2|6|3|4 $criteria
     * @psalm-param SORTARRIVAL, SORTCC, SORTDATE, SORTFROM, SORTSIZE, SORTSUBJECT, SORTTO
     *
     * @return array Mails ids
     */
    public function getSortMailsIds($criteria = 'ALL')
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $mails_ids = $this->instance->sortMails(SORTARRIVAL, true, $criteria);
        return $mails_ids;
    }

    /**
     * 获取文件夹
     *
     * @param string $search
     *
     * @return array 列表
     */
    public function getListingFolders($search = '*')
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $folders = $this->instance->getListingFolders($search);
        if ($this->debug) {
            $di->logger->debug(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['data' => $folders]);
        }
        return $folders;
    }

    /**
     * 获取文件夹
     *
     * @param string $search
     *
     * @return array 列表
     */
    public function getFolder($search = '*')
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $folders = $this->instance->getMailboxes($search);
        if ($this->debug) {
            $di->logger->debug(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['data' => $folders]);
        }
        return $folders;
    }

    /**
     * 获取邮件数
     *
     * @return int count
     */
    public function getMailsCount()
    {
        $di = \PhalApi\DI();
        if(!isset($this->instance)) return [];
        $count = $this->instance->countMails();
        if ($this->debug) {
            $di->logger->debug(__NAMESPACE__.DIRECTORY_SEPARATOR.__FUNCTION__, ['data' => $count]);
        }
        return $count;
    }
}