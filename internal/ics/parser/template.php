<?php
namespace itais\ics\parser;

class Template
{
    public $path;

    protected $parser;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function Compile($output_path)
    {
        $this->parser = new \itais\ics\parser\Parser;

        $this->parser->code               = file_get_contents($this->path);

        $this->parser->tag_start_start    = "{its: ";
        $this->parser->tag_start_end      = "}";
        $this->parser->tag_end_start      = "{/its: ";
        $this->parser->tag_end_end        = "}";
        $this->parser->delimeter          = " ";
        $this->parser->escape             = "\\";
        $this->parser->string             = "\"'";

        $this->parser->callback = $this->Callback;

        $this->parser->Parse();

        file_put_contents($output_path, $this->parser->output);
    }


    protected function Callback($tag)
    {
        $signal = new Signal;

        switch ($tag->command)
        {
            case "$":
                $signal->inline    = true;
                $signal->output    = $this->ParseExpression($tag->command);
                break;
            default:
                $this->Parser->SignalError("Unknown command '{$tag->command}'");
                break;
        }
    }
}

class ExpressionLexer
{
    public $expression;
    public $output;
    public $error_handler;

    protected $position;

    protected $expression_length;

    public function Lex()
    {
        $this->position    = 0;
        $this->output      = [];

        $this->expression_length    = strlen($expression);

        while ($this->position < $this->expression_length)
        {
            $nexttoken = $this->IsList(
            [
                "(", ")",
                "+", "-", "*", "/", "%",
                "&", "|", "^", "<<", ">>", "~",
                "<", "<=", ">", ">=", "==", "!=",
                "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                    "::", ".",
                "[", "]", ":", ",",
                "\"",
                    "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
                    "-",
                " ", "\t", "\r", "\n", "\0"
            ]);

            switch ($nexttoken)
            {
                case "(":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bracket_open;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ")":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bracket_cloes;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                

                
                case "+":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_add;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "-":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_subtract_literal_negative;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "*":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_multiply;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "/":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_divide;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "%":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_remainder;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "&":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_and;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "|":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_inclusiveor;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "^":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_exclusiveor;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;

                case "<<":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_leftshift;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                   
                case ">>":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_rightshift;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "~":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$bitwise_not;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                case "<":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_lessthan;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "<=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_lessthanequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ">":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_morethan;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ">=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_lessthanequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "==":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_equal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                    
                case "!=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$conditional_notequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j": case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t": case "u": case "v": case "w": case "x": case "y": case "z":
                case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J": case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T": case "U": case "V": case "W": case "X": case "Y": case "Z":
                
                    $this->LexIdentifier();

                    break;
                
                case "::":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$identifier_namespace;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ".":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$identifier_class_literal_floatingpoint;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "$":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$identifier_variable;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;

                case "@":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$identifier_static;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "[":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$list_open;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "]":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$list_close;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ":":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$list_pair;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ",":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$list_delimeter;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "\"":

                    $this->LexLiteralString();

                    break;
                
                case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":

                    $this->LexLiteralInteger();

                    break;


                
                case "-":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_subtract_literal_negative;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case " ": case "\t": case "\r": case "\n": case "\0":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::$arithmetic_subtract_literal_negative;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                default:
                    $this->position = $this->expression_length - 1;
                    break;
            }

            ++$this->position;
        }
    }

    protected function LexIdentifier()
    {
        $nextcharacter    = Read(1);
        $builder          = $nextcharacter;

        while ($nextcharacter = $this->IsList(["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"]))
        {
            $this->builder += $nextcharacter;

            ++$this->position;
        }

        $token                = new LexToken;
        $token->identifier    = LexTokenIdentifier::$identifier;
        $token->value         = $builder;

        $this->output[]       = $token;
    }

    protected function LexLiteralString()
    {
        ++$this->position;

        
    }

    protected function LexLiteralInteger()
    {
        
    }

    protected function IsList($list)
    {
        foreach ($list as $token)
        {
            if ($this->Is($token) === true) return $token;
        }

        return false;
    }

    protected function Is($token)
    {
        if ($this->Read(strlen($token_length)) === $token) return true;
        else return false;
    }

    protected function Read($length)
    {
        return substr($this->expression, $this->position, $length);
    }

    protected function Pass($length)
    {
        $output = $this->Read($length);
        $this->position += $length;

        if ($this->Overflow()) $this->error_handler("Position overflow");

        return $output;
    }

    protected function Overflow()
    {
        if ($this->position > $this->expression_length) return true;
        else return false;
    }
}

class LexToken
{
    public static $identifier    = null;
    public static $value         = null;
}

class LexTokenIdentifier
{
    public static $bracket_open                              = 0;
    public static $bracket_close                             = 1;

    public static $arithmetic_add                            = 2;
    public static $arithmetic_subtract_literal_negative      = 3;
    public static $arithmetic_multiply                       = 4;
    public static $arithmetic_divide                         = 5;
    public static $arithmetic_remainder                      = 6;

    public static $bitwise_and                               = 7;
    public static $bitwise_inclusiveor                       = 8;
    public static $bitwise_exclusiveor                       = 9;
    public static $bitwise_leftshift                         = 10;
    public static $bitwise_rightshift                        = 11;
    public static $bitwise_not                               = 12;

    public static $conditional_lessthan                      = 13;
    public static $conditional_lessthanequal                 = 14;
    public static $conditional_morethan                      = 15;
    public static $conditional_morethanequal                 = 16;
    public static $conditional_equal                         = 17;
    public static $conditional_notequal                      = 18;

    public static $identifier                                = 19;
    public static $identifier_namespace                      = 20;
    public static $identifier_class_literal_floatingpoint    = 21;
    public static $identifier_variable                       = 22;
    public static $identifier_static                         = 23;

    public static $list_open                                 = 24;
    public static $list_close                                = 25;
    public static $list_pair                                 = 26;
    public static $list_delimeter                            = 27;

    public static $literal_string                            = 28;
    public static $literal_integer                           = 29;
    public static $literal_negative                          = 30;

    public static $whitespace                                = 31;
}
