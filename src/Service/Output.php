<?php

namespace Miloshavlicek\DoctrineApiMapper\Service;

use Miloshavlicek\DoctrineApiMapper\Model\Response\Message;
use Symfony\Component\Translation\TranslatorInterface;

class Output
{

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $out = [
        'status' => true,
        'messages' => [],
        'result' => null,
        'meta' => []
    ];

    /**
     * Output constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    )
    {
        $this->translator = $translator;
    }

    /**
     * @param null|string $title
     * @param null|string $text
     */
    public function addWarning(?string $title, ?string $text = null)
    {
        $this->_addMessage(Message::TYPE_WARNING, $title, $text);
    }

    /**
     * @param string $type
     * @param null|string $title
     * @param null|string $text
     */
    private function _addMessage(string $type, ?string $title, ?string $text = null)
    {
        $this->out['messages'][] = (new Message($type, $text === null ? null : $title, $text === null ? $title : $text));
    }

    /**
     * @param Message ...$messages
     */
    public function addMessage(Message ...$messages)
    {
        foreach ($messages as $message) {
            $this->out['messages'][] = $message;
        }
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->out['result'];
    }

    /**
     * @param $result
     */
    public function setResult($result)
    {
        $this->out['result'] = $result;
    }

    /**
     * @param string $key
     * @param mexed $value
     */
    public function addResult(string $key, $value)
    {
        $this->out['result'][$key] = $value;
    }

    /**
     * @param string $prop
     * @return null
     */
    public function getMetaProperty(string $prop)
    {
        return $this->out['meta'][$prop] ?: null;
    }

    /**
     * @param string $prop
     * @param $value
     */
    public function setMetaProperty(string $prop, $value)
    {
        $this->out['meta'][$prop] = $value;
    }

    /**
     * @param \Exception $e
     */
    public function addException(\Exception $e)
    {
        $this->setStatus(false);
        $this->addError('Chyba serveru!');
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status)
    {
        $this->out['status'] = $status;
    }

    /**
     * @param null|string $title
     * @param null|string $text
     */
    public function addError(?string $title, ?string $text = null)
    {
        $this->setStatus(false);
        $this->_addMessage(Message::TYPE_ERROR, $title, $text);
    }

    /**
     * @param null|string $title
     * @param null|string $text
     */
    public function addSuccess(?string $title, ?string $text = null)
    {
        $this->setStatus(false);
        $this->_addMessage(Message::TYPE_SUCCESS, $title, $text);
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->mapOutput();
    }

    /**
     * @return array
     */
    private function mapOutput(): array
    {
        $out = $this->out;
        return $out;
    }


}