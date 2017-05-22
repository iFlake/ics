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

class ExpressionCompiler
{
    public $expression;
    public $error_handler;
    public $output;

    protected $position;
    protected $stack;

    protected $expression_length;

    public function Parse()
    {
        $this->position             = 0;
        $this->builder              = "";
        $this->stack                = [];

        $this->expression_length    = strlen($expression);

        while ($this->position < $this->expression_length)
        {
            switch ($this->Stack()->stage)
            {
                case ExpressionStage::unknown:
                    $entity = $this->IsMultiple([ExpectEntity::inverse, ExpectEntity::arithmetic_bitwise_not, ExpectEntity::reference_variable, ExpectEntity::reference_namespace, ExpectEntity::token]);
                    switch ($entity)
                    {
                        case ExpectEntity::inverse:
                            $expression_parser = new ExpressionCompiler;

                            $expression_parser->expression = $this->ReadAll();

                            $expression_parser->Parse();
                            
                            $this->output   .= "!{$expression_parser->output}";

                            break;
                        case ExpectEntity::arithmetic_bitwise_not:
                            $expression_parser = new Expression;

                            $expression_parser->expression = $this->ReadAll();

                            $expression_parser->Parse();

                            $this->output   .= "^{$expression_parser->output}";

                            break;
                        case ExpectEntity::reference_variable:
                            $this->output   .= "\$";

                            $this->Stack()->stage = ExpressionStage::reference;
                            break;
                        case ExpectEntity::reference_namespace:
                            $this->output   .= "\\";
                            
                            $this->Stack()->stage = ExpressionStage::reference;
                            break;
                        case ExpectEntity::token:
                            --$this->position;

                            $this->Stack()->stage = ExpressionStage::reference;
                            break;
                        default:
                            --$this->position;

                            $this->error_handler("Unexpected token " . $this->Char());
                            break;
                    }
                    break;
                case ExpressionStage::reference:
                    $entity = $this->IsMultiple([ExpectEntity::reference_variable, ExpectEntity::reference_namespace, ExpectEntity::reference_member, ExpectEntity::reference_member_static, ExpectEntity::token]);
                    
                    switch ($entity)
                    {
                        case ExpectEntity::reference_variable:
                            $this->output   .= "\$";

                            break;
                        case ExpectEntity::reference_namespace:
                            $this->output   .= "\\";

                            break;
                        case ExpectEntity::reference_member:
                            $this->output   .= "->";

                            break;
                        case ExpectEntity::reference_member_static:
                            $this->output   .= "::";

                            break;
                        
                        case ExpectEntity::token:
                            $this->output    .= $this->Char();
                            
                            break;

                        default:
                            --$this->position;
                            $this->Stack()->stage = ExpressionStage::unknown;

                            break;
                    }
                    break;
            }

            ++$this->position;
        }
    }

    
    protected function Stack()
    {
        return $this->stack[count($this->stack) - 1];
    }

    protected function Expect($entity)
    {
        $output    = "";

        while (!$this->Is($entity))
        {
            $output   .= substr($this->expression, $this->position, 1);

            $this->Pass();
        }

        return $output;
    }

    protected function ExpectMultiple($tokens)
    {
        $expects    = new Expects;

        while (($expects->token = $this->IsMultiple($tokens)) === false)
        {
            $expects->output   .= $this->Pass();
        }

        return $expects;
    }

    protected function Until($entity)
    {
        $expects    = new Expects;

        while (($expects->token = $this->IsMultiple($tokens)) !== false && $this->position < $this->expression_length)
        {
            $expects->output   .= $this->Pass();
        }

        return $expects;
    }

    protected function Is($entity, $increment = true)
    {
        foreach ($entity as $token)
        {
            if ($this->Char(strlen($token)) == $token)
            {
                return true;
            }
        }
        
        return false;
    }

    protected function IsMultiple($tokens, $increment = true)
    {
        foreach ($tokens as $token)
        {
            if ($this->Is($token, $increment)) return $token;
        }

        return false;
    }

    protected function Char($length = 1)
    {
        return substr($this->expression, $this->position, $length);
    }

    protected function Pass($length = 1)
    {
        $char    = $this->Char($length);

        $this->position += $length;

        return $char;
    }

    public function ReadAll()
    {
        $fragment = substr($this->expression, $this->position);

        $this->position = $this->expression_length - 1;

        return $fragment;
    }
}

class ExpressionStack
{
    public $stage      = ExpressionStage::unknown;
    public $builder    = "";
}

class ExpressionStage
{
    public static $unknown                            = 0;
    
    public static $arithmetic_add                     = 1;
    public static $arithmetic_subtract                = 2;
    public static $arithmetic_multiply                = 3;
    public static $arithmetic_divide                  = 4;
    public static $arithmetic_exponent                = 5;
    public static $arithmetic_root                    = 6;
    public static $arithmetic_remainder               = 7;
    
    public static $comparator_less                    = 8;
    public static $comparator_more                    = 9;
    public static $comparator_lessthanequal           = 10;
    public static $comparator_morethanequal           = 11;
    public static $comparator_equal                   = 12;
    public static $comparator_notequal                = 13;

    public static $arithmetic_bitwise_and             = 14;
    public static $arithmetic_bitwise_inclusive_or    = 15;
    public static $arithmetic_bitwise_exclusive_or    = 16;
    public static $arithmetic_bitwise_leftshift       = 17;
    public static $arithmetic_bitwise_rightshift      = 18;
    public static $arithmetic_bitwise_not             = 19;
    
    public static $reference                          = 20;
    public static $call                               = 21;
}

class ExpectEntity
{
    public static $token                              = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
    public static $integer                            = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

    public static $float                              = ["."];

    public static $inverse                            = ["!"];

    public static $arithmetic_add                     = ["+"];
    public static $arithmetic_subtract                = ["-"];
    public static $arithmetic_multiply                = ["*"];
    public static $arithmetic_divide                  = ["/"];
    public static $arithmetic_exponent                = ["^^"];
    public static $arithmetic_root                    = ["!^^"];
    public static $arithmetic_remainder               = ["%"];
    
    public static $comparator_less                    = ["<"];
    public static $comparator_more                    = [">"];
    public static $comparator_lessthanequal           = ["<="];
    public static $comparator_morethanequal           = [">="];
    public static $comparator_equal                   = ["=="];
    public static $comparator_notequal                = ["!="];

    public static $arithmetic_bitwise_and             = ["&"];
    public static $arithmetic_bitwise_inclusive_or    = ["|"];
    public static $arithmetic_bitwise_exclusive_or    = ["^"];
    public static $arithmetic_bitwise_leftshift       = ["<<"];
    public static $arithmetic_bitwise_rightshift      = [">>"];
    public static $arithmetic_bitwise_not             = ["~"];
    
    public static $reference_variable                 = ["$"];
    public static $reference_namespace                = ["::"];
    public static $reference_member                   = ["."];
    public static $reference_member_static            = ["@"];

    public static $call_start                         = ["("];
    public static $call_end                           = [")"];
    public static $call_parameters_delimeter          = [","];

    public static $whitespace                         = [" ", "\t", "\r", "\n", "\0"];
}
