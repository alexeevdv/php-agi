<?php

namespace alexeevdv\agi;

/**
 * Class Response
 * @package alexeevdv\agi
 */
class Response
{
    const CODE_SUCCESS = 200;
    const CODE_ERROR = 500;
    const CODE_IVALID_COMMAND = 510;
    const CODE_COMMAND_NOT_PERMITED = 511;
    const CODE_END_OF_PROPER_USAGE = 520;

    /**
     * @var int
     */
    private $code;

    /**
     * @var bool
     */
    private $result;

    /**
     * @var mixed
     */
    private $data;

    /**
     * Response constructor.
     * @param int $code
     * @param bool $result
     * @param mixed $data
     */
    public function __construct($code, $result = false, $data = '')
    {
        $this->code = $code;
        $this->result = $result;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param bool $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * TODO temporary method for backward compatibility. SHOULD BE REMOVED
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->code,
            'result' => $this->result ? 0 : -1,
            'data' => $this->data,
        ];
    }
}
