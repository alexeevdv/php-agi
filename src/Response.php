<?php

namespace alexeevdv\agi;

class Response
{
    const CODE_SUCCESS = 200;
    const CODE_ERROR = 500;
    const CODE_IVALID_COMMAND = 510;
    const CODE_COMMAND_NOT_PERMITED = 511;
    const CODE_END_OF_PROPER_USAGE = 520;

    private $code;

    private $result;

    private $data;

    public function __construct($code, $result = false, $data = '')
    {
        $this->code = $code;
        $this->result = $result;
        $this->data = $data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    // TODO temporary method for backward compatibility. SHOULD BE REMOVED
    public function toArray()
    {
        return [
            'code' => $this->code,
            'result' => $this->result ? 0 : -1,
            'data' => $this->data,
        ];
    }
}
