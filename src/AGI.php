<?php

namespace alexeevdv\agi;

use Psr\Log\LoggerInterface;

class AGI
{
    /**
     * @var InputStreamInterface
     */
    private $input;

    /**
     * @var OutputStreamInterface
     */
    private $output;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $requestVariables = [];

    public function __construct(
        InputStreamInterface $input,
        OutputStreamInterface $output,
        LoggerInterface $logger = null
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->logger = $logger ? $logger : new DummyLogger;
        $this->readRequestVariables();
    }

    protected function readRequestVariables()
    {
        $string = $this->input->readLine();
        // If there is no input for some reason
        if ($string === null) {
            // TODO maybe throw exception?
            return;
        }

        while ($string != "\n") {
            // TODO handle if not found
            $colonPosition = strpos($string, ':');
            $name = substr($string, 0, $colonPosition);
            $value = substr($string, $colonPosition + 1);
            $this->requestVariables[$name] = trim($value);
            $string = $this->input->readLine();
            $this->getLogger()->debug($string);
        }
    }

    /**
     * Sends $message to the Asterisk console via the 'verbose' message system.
     *
     * If the Asterisk verbosity level is $level or greater, send $message to the console.
     *
     * The Asterisk verbosity system works as follows. The Asterisk user gets to set the desired verbosity at startup
     * time or later using the console 'set verbose' command. Messages are displayed on the console if their verbose
     * level is less than or equal to desired verbosity set by the user. More important messages should have a low
     * verbose level; less important messages should have a high verbose level.
     *
     * @link http://www.voip-info.org/wiki-verbose
     * @param string $message
     * @param integer $level from 1 to 4
     * @return array, see evaluate for return information.
     */
    public function verbose($message, $level = 1)
    {
        $this->getLogger()->debug($message);
        foreach (explode("\n", str_replace("\r\n", "\n", print_r($message, true))) as $msg) {
            $ret = $this->evaluate("VERBOSE \"$msg\" $level");
        }
        return $ret;
    }

    /**
     * Evaluate an AGI command.
     *
     * @access private
     * @param string $command
     * @return array ('code'=>$code, 'result'=>$result, 'data'=>$data)
     */
    public function evaluate($command)
    {
        $this->getLogger()->debug($command);

        if (!$this->output->writeLine(trim($command))) {
            $this->getLogger()->debug('Write failed');
            return (new Response(Response::CODE_ERROR))->toArray();
        }
        $this->output->flush();

        // Read result.  Occasionally, a command return a string followed by an extra new line.
        // When this happens, our script will ignore the new line, but it will still be in the
        // buffer.  So, if we get a blank line, it is probably the result of a previous
        // command.  We read until we get a valid result or asterisk hangs up.  One offending
        // command is SEND TEXT.
        $count = 0;
        do {
            $str = trim($this->input->readLine());

            //пропускаем сообщение HANGUP от астериска
            if (preg_match('/^HANGUP$/', $str) == 1) {
                $str = trim($this->input->readLine());
            }

            $this->getLogger()->debug($str);
        } while ($str == '' && $count++ < 5);

        if ($count >= 5) {
            return (new Response(Response::CODE_ERROR))->toArray();
        }

        // parse result
        $ret['code'] = substr($str, 0, 3);
        $str = trim(substr($str, 3));

        if ($str{0} == '-') {// we have a multiline response!
            $count = 0;
            $str = substr($str, 1) . "\n";
            $line = $this->input->readLine();
            while (substr($line, 0, 3) != $ret['code'] && $count < 5) {
                $str .= $line;
                $line = $this->input->readLine();
                $count = (trim($line) == '') ? $count + 1 : 0;
            }
            if ($count >= 5) {
                return (new Response(Response::CODE_ERROR))->toArray();
            }
        }

        $ret['result'] = null;
        $ret['data'] = '';
        if ($ret['code'] != Response::CODE_SUCCESS) {
            $ret['data'] = $str;
            $this->getLogger()->debug(print_r($ret, true));
        } else { // normal AGIRES_OK response
            // TODO extract to separate method
            $parse = explode(' ', trim($str));
            $in_token = false;
            foreach ($parse as $token) {
                if ($in_token) {// we previously hit a token starting with ')' but not ending in ')'
                    $ret['data'] .= ' ' . trim($token, '() ');
                    if ($token{strlen($token)-1} == ')') {
                        $in_token = false;
                    }
                } elseif ($token{0} == '(') {
                    if ($token{strlen($token)-1} != ')') {
                        $in_token = true;
                    }
                    $ret['data'] .= ' ' . trim($token, '() ');
                } elseif (strpos($token, '=')) {
                    $token = explode('=', $token);
                    $ret[$token[0]] = $token[1];
                } elseif ($token != '') {
                    $ret['data'] .= ' ' . $token;
                }
            }
            $ret['data'] = trim($ret['data']);
        }

        // log some errors
        if ($ret['result'] < 0) {
            $this->getLogger()->debug("$command returned {$ret['result']}");
        }

        return $ret;
    }

    /**
     * Executes the specified Asterisk application with given options.
     *
     * @link http://www.voip-info.org/wiki-exec
     * @link http://www.voip-info.org/wiki-Asterisk+-+documentation+of+application+commands
     * @param string $application
     * @param mixed $options
     * @return array, see evaluate for return information. ['result'] is whatever the application returns, or -2 on
     * failure to find application
     */
    public function exec($application, $options)
    {
        $this->getLogger()->debug($application);
        if (is_array($options)) {
            $options = join('|', $options);
        }
        return $this->evaluate("EXEC $application $options");
    }

    /**
     * Fetch the value of a variable.
     *
     * Does not work with global variables. Does not work with some variables that are generated by modules.
     *
     * @link http://www.voip-info.org/wiki-get+variable
     * @link http://www.voip-info.org/wiki-Asterisk+variables
     * @param string $variable name
     * @param boolean $getvalue return the value only
     * @return array, see evaluate for return information. ['result'] is 0 if variable hasn't been set, 1 if it has.
     * ['data'] holds the value. returns value if $getvalue is TRUE
     */
    public function getVariable($variable, $getvalue = false)
    {
        $result = $this->evaluate("GET VARIABLE $variable");
        if ($getvalue === false) {
            return $result;
        }
        return $result['data'];
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getRequestVariable($name)
    {
        if (isset($this->requestVariables[$name])) {
            return $this->requestVariables[$name];
        }
        return null;
    }

    /**
     * Hangup the specified channel. If no channel name is given, hang up the current channel.
     *
     * With power comes responsibility. Hanging up channels other than your own isn't something
     * that is done routinely. If you are not sure why you are doing so, then don't.
     *
     * @link http://www.voip-info.org/wiki-hangup
     * @example examples/dtmf.php Get DTMF tones from the user and say the digits
     * @example examples/input.php Get text input from the user and say it back
     * @example examples/ping.php Ping an IP address
     *
     * @param string $channel
     * @return array, see evaluate for return information. ['result'] is 1 on success, -1 on failure.
     */
    public function hangup($channel = '')
    {
        return $this->evaluate("HANGUP $channel");
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
}
