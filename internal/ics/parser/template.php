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

class ExpressionTranslator
{
    public $expression;
    public $output;
    public $error_handler;

    protected $position;
    protected $stack = [];
    
    protected $lexer;
    protected $lexed_expression_length;


    public function __call($name, $parameters)
    {
        if (is_callable($this->{$name}))
        {
            return call_user_func_array($this->{$name}, $parameters);
        }
    }


    public function Translate()
    {
        $this->lexer                   = new ExpressionLexer;

        $this->lexer->expression       = $this->expression;
        $this->lexer->error_handler    = $this->error_handler;

        $this->lexer->Lex();


        $this->lexed_expression_length = count($this->lexer->output);

        while ($this->position < $this->lexed_expression_length)
        {
            if ($this->Shift() == false) $this->Reduce();
        }

        if (count($this->stack) != 0) return $this->error_handler("Reached end of expression, no reduction match found");
    }

    protected function Shift()
    {
        
    }

    protected function Reduce()
    {

    }
}

class ExpressionLexer
{
    public $expression;
    public $output;
    public $error_handler;

    protected $position;

    protected $expression_length;



    public function __call($name, $parameters)
    {
        if (is_callable($this->{$name}))
        {
            return call_user_func_array($this->{$name}, $parameters);
        }
    }



    public function Lex()
    {
        $this->position    = 0;
        $this->output      = [];

        $this->expression_length    = strlen($this->expression);

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

            if ($nexttoken === false) return;

            switch ($nexttoken)
            {
                case "(":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bracket_open;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ")":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bracket_close;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                

                
                case "+":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::arithmetic_add;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "-":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::arithmetic_subtract_literal_negative;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "*":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::arithmetic_multiply;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "/":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::arithmetic_divide;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "%":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::arithmetic_remainder;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "&":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_and;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "|":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_inclusiveor;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "^":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_exclusiveor;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;

                case "<<":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_leftshift;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                   
                case ">>":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_rightshift;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "~":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::bitwise_not;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                case "<":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_lessthan;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "<=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_lessthanequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ">":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_morethan;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ">=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_lessthanequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "==":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_equal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                    
                case "!=":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_notequal;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j": case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t": case "u": case "v": case "w": case "x": case "y": case "z":
                case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J": case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T": case "U": case "V": case "W": case "X": case "Y": case "Z":
                
                    $this->LexIdentifier();

                    break;
                
                case "::":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::identifier_namespace;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ".":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::identifier_class_literal_floatingpoint;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "$":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::identifier_variable;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;

                case "@":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::identifier_static;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "[":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::list_open;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case "]":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::list_close;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ":":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::list_pair;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                
                case ",":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::list_delimeter;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;
                


                case "\"":

                    $this->LexLiteralString();

                    break;
                
                case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":

                    $this->LexLiteralInteger();

                    break;


                
                case " ": case "\t": case "\r": case "\n": case "\0":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::whitespace;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;


                
                default:
                    $this->position = $this->expression_length - 1;
                    break;
            }

            $this->position += strlen($nexttoken);
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
        $token->identifier    = LexTokenIdentifier::identifier;
        $token->value         = $builder;

        $this->output[]       = $token;
        
        --$this->position;
    }

    protected function LexLiteralString()
    {
        ++$this->position;

        $builder    = "";

        while (true)
        {
            $nextcharacter = $this->IsList(
            [
                "\"",
                "\\"
            ]);

            if ($nextcharacter === false) $builder .= $this->Pass(1);
            else
            {
                switch ($nextcharacter)
                {
                    case "\"":
                        $token                = new LexToken;
                        $token->identifier    = stripcslashes(LexTokenIdentifier::literal_string);
                        $token->value         = $builder;

                        $this->output[]       = $token;

                        return;
                    
                    case "\\":
                        $builder .= $this->Pass(2);

                        break;
                }
            }
        }
    }

    protected function LexLiteralInteger()
    {
        $nextcharacter    = $this->Pass(1);
        $builder          = $nextcharacter;

        while ($nextcharacter = $this->IsList(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]))
        {
            $builder .= $nextcharacter;

            ++$this->position;
        }

        $token                = new LexToken;
        $token->identifier    = intval(LexTokenIdentifier::literal_integer);
        $token->value         = $builder;

        $this->output[]       = $token;
        --$this->position;
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
        if ($this->Read(strlen($token)) === $token) return true;
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

        if ($this->Overflow()) throw new \Exception("Position overflow");// $this->error_handler("Position overflow");

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
    public $identifier    = null;
    public $value         = null;
}

class LexTokenIdentifier
{
    public const bracket_open                              = 0;
    public const bracket_close                             = 1;

    public const arithmetic_add                            = 2;
    public const arithmetic_subtract_literal_negative      = 3;
    public const arithmetic_multiply                       = 4;
    public const arithmetic_divide                         = 5;
    public const arithmetic_remainder                      = 6;

    public const bitwise_and                               = 7;
    public const bitwise_inclusiveor                       = 8;
    public const bitwise_exclusiveor                       = 9;
    public const bitwise_leftshift                         = 10;
    public const bitwise_rightshift                        = 11;
    public const bitwise_not                               = 12;

    public const conditional_lessthan                      = 13;
    public const conditional_lessthanequal                 = 14;
    public const conditional_morethan                      = 15;
    public const conditional_morethanequal                 = 16;
    public const conditional_equal                         = 17;
    public const conditional_notequal                      = 18;

    public const identifier                                = 19;
    public const identifier_namespace                      = 20;
    public const identifier_class_literal_floatingpoint    = 21;
    public const identifier_variable                       = 22;
    public const identifier_static                         = 23;

    public const list_open                                 = 24;
    public const list_close                                = 25;
    public const list_pair                                 = 26;
    public const list_delimeter                            = 27;

    public const literal_string                            = 28;
    public const literal_integer                           = 29;

    public const whitespace                                = 30;
}
