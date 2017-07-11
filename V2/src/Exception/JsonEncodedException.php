<?php



namespace Extractor\Exception;


/**
 * Class JsonEncodedException
 * @package Extractor\Exception
 */
class JsonEncodedException extends \Exception
{



    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null           $message
     * @param int            $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }




    /**
     * Returns the json decoded message.
     *
     * @return mixed
     */
    public function getDecodedMessage()
    {
        return json_decode($this->getMessage(),true);
    }
}