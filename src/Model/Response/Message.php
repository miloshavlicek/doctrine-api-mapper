<?php

namespace Miloshavlicek\DoctrineApiMapper\Model;

class Message
{
    const TYPE_ERROR = 'err';
    const TYPE_WARNING = 'warn';
    const TYPE_SUCCESS = 'ok';
    /** @var string */
    public $title;
    /** @var string */
    public $text;
    /** @var string */
    private $type;

    /**
     * Message constructor.
     * @param null|string $type
     * @param null|string $title
     * @param null|string $text
     * @throws \Exception
     */
    public function __construct(?string $type = null, ?string $title = null, ?string $text = null)
    {
        $this->setType($type);
        $this->title = $title;
        $this->text = $text;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type)
    {
        if (!in_array($type, [self::TYPE_ERROR, self::TYPE_SUCCESS, self::TYPE_WARNING])) {
            throw new \Exception('Invalid argument');
        }
        $this->type = $type;
    }

}