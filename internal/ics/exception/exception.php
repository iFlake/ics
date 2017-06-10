<?php
namespace itais\ics\exception;

class ICSException extends \Exception
{
    public $initiator;
    public $code;

    public function __construct($message, $initiator, $code)
    {
        $this->initiator = $initiator;
        $this->code = $code;
        parent::__construct($message);
    }

    public function __toString()
    {
        return $this->initiator . "/" . $this->code;
    }
}
