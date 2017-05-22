<?php
namespace itais\ics\parser;

class Parser
{
    public $code;
    public $output;

    public $tag_start_start              = "<";
    public $tag_start_end                = ">";
    public $tag_end_start                = "</";
    public $tag_end_end                  = ">";
    public $delimeter                    = " ";
    public $escape                       = "\\";
    public $string                       = "\"'";

    public $callback                     = null;

    protected $position                  = 0;
    protected $stack                     = [];

    protected $code_length;
    protected $tag_start_start_length;
    protected $tag_start_end_length;
    protected $tag_end_start_length;
    protected $tag_end_end_length;

    protected $escape_array;


    public function __call($name, $parameters)
    {
        if (is_callable($this->{$name}))
        {
            return call_user_func_array($this->{$name}, $parameters);
        }
    }
    

    public function Parse()
    {
        $this->code_length                = strlen($this->code);
        $this->tag_start_start_length     = strlen($this->tag_start_start);
        $this->tag_start_end_length       = strlen($this->tag_start_end);
        $this->tag_end_start_length       = strlen($this->tag_end_start);
        $this->tag_end_end_length         = strlen($this->tag_end_end);

        $this->escape_array               = str_split($this->escape);
        
        $this->CreateElement("", "");

        while ($this->position < $this->code_length)
        {
            if ($this->Is($this->tag_start_start, false) && !$this->Is($this->tag_end_start, false))
            {
                $this->Pass($this->tag_start_start_length);

                $next_token = $this->ExpectMultiple([$this->tag_start_end, $this->delimeter]);

                $tag             = new Tag;
                $tag->type       = TagType::start;
                $tag->command    = $next_token->output;

                if ($next_token->token == $this->delimeter)
                {
                    $tag->parameters = $this->Expect($this->tag_start_end);
                }

                $result = $this->callback($tag);

                if ($result->inline == true)
                {
                    $this->AppendCurrent($result->output);
                }
                else
                {
                    $this->CreateElement($tag->command, $tag->parameters);
                }
            }
            else if ($this->Is($this->tag_end_start, false))
            {
                $this->Pass($this->tag_end_start_length);

                $command = $this->Expect($this->tag_end_end);

                $this->CloseElement($command);
            }
            else
            {
                $this->AppendCurrent($this->Pass());
            }
        }

        if (count($this->stack) != 1) $this->Error("Mismatched starting tag", $last_element->position);

        $last_element    = array_pop($this->stack);
        $this->output    = $last_element->output;
    }


    public function SignalError($error)
    {
        $location = $this->Location($this->position);
        throw new \Exception("{$error} at line {$location->line} and character {$location->character} (index {$location->index})");
    }


    protected function CreateElement($tag, $parameters)
    {
        $element                = new Element;
        
        $element->position      = $this->position;
        $element->tag           = $tag;
        $element->parameters    = $parameters;

        $this->stack[]          = $element;
    }

    protected function CloseElement($tag)
    {
        $last_element = array_pop($this->stack);
        if (!$last_element || $last_element->tag == "")
        {
            $this->Error("Mismatched closing tag", $this->position);
        }

        if ($last_element->tag != $tag)
        {
            $this->Error("Expected {$last_element->tag}, got {$tag}", $this->position);
        }

        $tag                = new Element;

        $tag->type          = TagType::end;
        $tag->command       = $last_element->tag;
        $tag->parameters    = $last_element->parameters;
        $tag->content       = $last_element->output;

        $this->AppendCurrent($this->callback($tag)->output);
    }

    protected function AppendCurrent($output)
    {
        $this->stack[count($this->stack) - 1]->output .= $output;
    }

    protected function Is($token, $increment = true)
    {
        $token_length = strlen($token);
        if ($this->Char($token_length) == $token)
        {
            if ($increment == true)
            {
                $this->position += $token_length;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function IsMultiple($tokens, $increment =true)
    {
        foreach ($tokens as $token)
        {
            if ($this->Is($token, $increment)) return $token;
        }
        return false;
    }

    protected function Expect($token)
    {
        $output          = "";

        $string          = null;
        $string_array    = str_split($this->string);

        $escape          = false;
        $escape_array    = str_split($this->escape);

        $position        = $this->position;

        while (true)
        {
            $char = $this->Char();
            if ($escape == false && array_search($char, $escape_array) !== false)
            {
                $escape    = true;
            }
            else if ($escape == false && $string === null && array_search($char, $string_array) !== false)
            {
                $string    = $char;
                $output   .= $char;
            }
            else
            {
                if ($escape == false && $string === null)
                {
                    if ($this->Is($token))
                    {
                        return $output;
                    }
                }
                else if ($char == $string)
                {
                    $string = null;
                }

                if ($escape == true)
                {
                    $escape = false;
                }

                $output .= $char;
            }

            ++$this->position;

            if ($this->Overflow()) $this->Error("Expected '{$token}'", $position);
        }
        return $output;
    }

    protected function ExpectMultiple($tokens)
    {
        $expects         = new Expects;

        $string          = null;
        $string_array    = str_split($this->string);

        $escape          = false;
        $escape_array    = str_split($this->escape);

        $position        = $this->position;

        while (($expects->token = $this->IsMultiple($tokens)) === false)
        {
            $char = $this->Char();

            if ($escape == false && array_search($char, $escape_array) !== false)
            {
                $escape    = true;
            }
            else if ($escape == false && $string === null && array_search($char, $string_array) !== false)
            {
                $string             = $char;
                $expects->output   .= $char;
            }
            else
            {
                if ($escape == false && $string === null)
                {
                    if ($this->IsMultiple($tokens))
                    {
                        return $expects->output;
                    }
                }
                else if ($string !== null && $char == $string)
                {
                    $string = null;
                }
                $expects->output .= $char;
            }

            ++$this->position;

            if ($this->Overflow()) $this->Error("Expected [list]", $position);
        }
        
        return $expects;
    }

    protected function Char($length = 1)
    {
        if ($this->code_length - $this->position < $length) return "";
        return substr($this->code, $this->position, $length);
    }

    protected function Pass($length = 1)
    {
        $char = $this->Char($length);
        $this->position += $length;
        return $char;
    }

    protected function Overflow()
    {
        if ($this->position > $this->code_length)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function Location($position)
    {
        $this->code_array = str_split($this->code);

        $line         = 1;
        $character    = 0;

        foreach ($this->code_array as $idx=>$char)
        {
            if ($idx >= $position) break;
            switch ($char)
            {
                case "\n":
                    ++$line;
                    $character = 0;
                    break;
                case "\r":
                    break;
                default:
                    ++$character;
                    break;
            }
        }

        $location               = new Location;
        $location->line         = $line;
        $location->character    = $character;
        $location->index        = $position;

        return $location;
    }

    protected function Error($error, $position)
    {
        $location = $this->Location($position);
        throw new \Exception("{$error} at line {$location->line} and character {$location->character} (index {$location->index})");
    }

    protected function Next()
    {
        $info = array_pop($this->stack);
        Parse();
    }
    
    protected function NextInline()
    {
        Parse();
    }
}

class Signal
{
    public $inline    = false;
    public $output    = "";
}

class Tag
{
    public $type          = null;
    public $command       = "";
    public $parameters    = "";
    public $content       = "";
}

class Location
{
    public $line         = 1;
    public $character    = 0;
    public $index        = 0;
}

class TagType
{
    const start    = 0;
    const end      = 1;
}

class Element
{
    public $position      = 0;
    public $tag           = "";
    public $parameters    = "";
    public $output        = "";
}

class Expects
{
    public $token     = "";
    public $output    = "";
}
