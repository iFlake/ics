<?php
namespace itais\ics\parser;

class TemplateCompiler
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
                $signal->inline               = true;

                $translator                   = new ExpressionTranslator;

                $translator->expression       = $tag->parameters;
                $translator->error_handler    = $this->parser->SignalError;

                $translator->Translate();

                $signal->output               = "<?php {$translator->output}; ?>";

                break;
            
            case "asn":
                $signal->inline                          = true;

                $sections                                = explode("::::", $tag->parameters, 2);

                $identifier_translator                   = new ExpressionTranslator;

                $identifier_translator->expression       = $sections[0];
                $identifier_translator->error_handler    = $this->parser->SignalError;

                $identifier_translator->Translate();

                $value_translator                        = new ExpressionTranslator;

                $value_translator->expression            = $sections[1];
                $value_translator->error_handler         = $this->parser->SignalError;

                $value_translator->Translate();

                $signal->output               = "<?php {$identifier_translator->output} = {$value_translator->output}; ?>";

                break;
            
            case "if":
                $signal->inline               = false;

                $translator                   = new ExpressionTranslator;

                $translator->expression       = $tag->parameters;
                $translator->error_handler    = $this->parser->SignalError;

                $translator->Translate();

                $signal->output               = "<?php if ({$translator->output}) { ?> {$tag->content} <?php } ?>";

                break;
            
            case "else":
                $signal->inline               = false;

                $signal->output               = "<?php else { ?> {$tag->content} <?php } ?>";

                break;

            case "switch":
                $signal->inline               = false;

                $translator                   = new ExpressionTranslator;

                $translator->expression       = $tag->parameters;
                $translator->error_handler    = $this->parser->SignalError;

                $translator->Translate();

                $signal->output               = "<?php switch ({$translator->output}) { {$tag->content} } ?>";

                break;

            case "case":
                $signal->inline               = false;

                $translator                   = new ExpressionTranslator;

                $translator->expression       = $tag->parameters;
                $translator->error_handler    = $this->parser->SignalError;

                $translator->Translate();

                $signal->output               = "case {$translator->output}: { ?> {$tag->content} <?php }";

                break;

            case "default":
                $signal->inline               = false;

                $signal->output               = "default: { ?> {$tag->content} <?php }";

                break;

            case "break":
                $signal->inline               = true;

                $signal->output               = "<?php break; ?>";

                break;

            case "while":
                $signal->inline               = false;

                $translator                   = new ExpressionTranslator;

                $translator->expression       = $tag->parameters;
                $translator->error_handler    = $this->parser->SignalError;

                $translator->Translate();

                $signal->output               = "while ({$translator->output}) { ?> {$tag->content} <?php }";

                break;

            case "foreach":
                $signal->inline                         = false;

                $in_sections                            = explode(" in ", $tag->parameters, 2);
                $iv_sections                            = explode("->", $in_sections[0], 2);



                $index_translator                       = new ExpressionTranslator;

                $index_translator->expression           = $iv_sections[0];
                $index_translator->error_handler        = $this->parser->SignalError;

                $index_translator->Translate();


                $value_translator                       = new ExpressionTranslator;

                $value_translator->expression           = $iv_sections[1];
                $value_translator->error_handler        = $this->parser->SignalError;

                $value_translator->Translate();

                
                $reference_translator                   = new ExpressionTranslator;

                $reference_translator->expression       = $in_sections[1];
                $reference_translator->error_handler    = $this->parser->SignalError;

                $reference_translator->Translate();



                $signal->output                     = "foreach ({$reference_translator->output} as {$index_translator->output} => {$value_translator->output}) { ?> {$tag->content} <?php }";

                break;





            default:
                $this->Parser->SignalError("Unknown command '{$tag->command}'");

                break;
        }

        return $signal;
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

    protected $matches;


    public function __call($name, $parameters)
    {
        if (is_callable($this->{$name}))
        {
            return call_user_func_array($this->{$name}, $parameters);
        }
    }


    public function Translate()
    {
        $this->lexer                      = new ExpressionLexer;

        $this->lexer->expression          = $this->expression;
        $this->lexer->error_handler       = $this->error_handler;

        $this->lexer->Lex();

        $this->lexed_expression_length    = count($this->lexer->output);


        $parentheses    = 0;
        $lists          = 1;


        $this->position = 0;


        while ($this->position < $this->lexed_expression_length)
        {;
            switch ($this->lexer->output[$this->position]->identifier)
            {
                case LexTokenIdentifier::bracket_open:
                    ++$parentheses;
                    $this->output .= "(";

                    break;
                
                case LexTokenIdentifier::bracket_close:
                    --$parentheses;

                    if ($parentheses < 0) return $this->error_handler("Mismatched parentheses");

                    $this->output .= ")";

                    break;


                
                case LexTokenIdentifier::arithmetic_add:
                    $this->output .= "+";

                    break;
                
                case LexTokenIdentifier::arithmetic_subtract_literal_negative:
                    $this->output .= "-";
                    
                    break;
                
                case LexTokenIdentifier::arithmetic_multiply:
                    $this->output .= "*";

                    break;
                
                case LexTokenIdentifier::arithmetic_divide:
                    $this->output .= "/";

                    break;
                
                case LexTokenIdentifier::arithmetic_remainder:
                    $this->output .= "%";

                    break;


                
                case LexTokenIdentifier::conditional_lessthan:
                    $this->output .= "<";

                    break;
                
                case LexTokenIdentifier::conditional_lessthanequal:
                    $this->output .= "<=";

                    break;
                
                case LexTokenIdentifier::conditional_morethan:
                    $this->output .= ">";

                    break;
                
                case LexTokenIdentifier::conditional_morethanequal:
                    $this->output .= ">=";

                    break;
                
                case LexTokenIdentifier::conditional_and:
                    $this->output .= "&&";

                    break;
                
                case LexTokenIdentifier::conditional_or:
                    $this->output .= "||";

                    break;


                
                case LexTokenIdentifier::bitwise_and:
                    $this->output .= "&";

                    break;
                
                case LexTokenIdentifier::bitwise_inclusiveor:
                    $this->output .= "|";

                    break;
                
                case LexTokenIdentifier::bitwise_exclusiveor:
                    $this->output .= "^";

                    break;
                
                case LexTokenIdentifier::bitwise_leftshift:
                    $this->output .= "<<";

                    break;
                
                case LexTokenIdentifier::bitwise_rightshift:
                    $this->output .= ">>";

                    break;
                
                case LexTokenIdentifier::bitwise_not:
                    $this->output .= "~";

                    break;
                


                case LexTokenIdentifier::identifier:
                    $this->output .= $this->lexer->output[$this->position]->value;

                    break;
                
                case LexTokenIdentifier::identifier_namespace:
                    $this->output .= "\\";

                    break;
                
                case LexTokenIdentifier::identifier_class_literal_floatingpoint:
                    $this->output .= ".";
                    
                    break;
                
                case LexTokenIdentifier::identifier_variable:
                    $this->output .= "\$";

                    break;
                
                case LexTokenIdentifier::identifier_static:
                    $this->output .= "::";
                    
                    break;


                    
                case LexTokenIdentifier::list_open:
                    $this->output .= "[";

                    break;
                
                case LexTokenIdentifier::list_close:
                    $this->output .= "]";

                    break;
                
                case LexTokenIdentifier::list_pair:
                    $this->output .= "=>";

                    break;
                
                case LexTokenIdentifier::list_delimeter:
                    $this->output .= ",";
                    
                    break;



                case LexTokenIdentifier::literal_string:
                    $this->output .= "\"" . addcslashes($this->lexer->output[$this->position]->value, "\\") . "\"";

                    break;
                
                case LexTokenIdentifier::literal_integer:
                    $this->output .= (int)$this->lexer->output[$this->position]->value;

                    break;
                


                case LexTokenIdentifier::whitespace:
                    $this->output .= "\n";

                    break;
            }
            
            ++$this->position;
        }

        if (count($this->stack) != 0) return $this->error_handler("Reached end of expression, no reduction match found");
    }
}

class ExpressionLexer
{
    public $expression;
    public $output;
    public $error_handler;

    public $dictionary;

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
        if ($this->position == null) $this->position    = 0;
        $this->output      = [];

        $this->expression_length    = strlen($this->expression);

        if ($this->dictionary == null) $this->dictionary =
        [
            "(", ")",
            "+", "-", "*", "/", "%",
            "<=", "<", ">=", ">", "==", "!=", "&&", "||",
            "&", "|", "^", "<<", ">>", "~",
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                "::", ".",
            "[", "]", ":", ",",
            "\"",
                "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
                "-",
            " ", "\t", "\r", "\n", "\0"
        ];

        while ($this->position < $this->expression_length)
        {
            $nexttoken = $this->IsList($this->dictionary);

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

                case "&&":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_and;
                    $token->value         = $nexttoken;

                    $this->output[]       = $token;

                    break;

                case "||":
                    $token                = new LexToken;
                    $token->identifier    = LexTokenIdentifier::conditional_or;
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

                    $this->LexWhitespace();

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
        $nextcharacter    = $this->Read(1);
        $builder          = "";

        while ($nextcharacter = $this->IsList(["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"]))
        {
            $builder .= $nextcharacter;

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

    protected function LexWhitespace()
    {
        $nextcharacter    = $this->Pass(1);
        $builder          = $nextcharacter;

        while ($nextcharacter = $this->IsList([" ", "\t", "\r", "\n", "\0"]))
        {
            $builder .= $nextcharacter;

            ++$this->position;
        }

        $token                = new LexToken;
        $token->identifier    = intval(LexTokenIdentifier::whitespace);
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

    public const conditional_lessthan                      = 7;
    public const conditional_lessthanequal                 = 8;
    public const conditional_morethan                      = 9;
    public const conditional_morethanequal                 = 10;
    public const conditional_equal                         = 11;
    public const conditional_notequal                      = 12;
    public const conditional_and                           = 13;
    public const conditional_or                            = 14;

    public const bitwise_and                               = 15;
    public const bitwise_inclusiveor                       = 16;
    public const bitwise_exclusiveor                       = 17;
    public const bitwise_leftshift                         = 18;
    public const bitwise_rightshift                        = 19;
    public const bitwise_not                               = 20;

    public const identifier                                = 21;
    public const identifier_namespace                      = 22;
    public const identifier_class_literal_floatingpoint    = 23;
    public const identifier_variable                       = 24;
    public const identifier_static                         = 25;

    public const list_open                                 = 26;
    public const list_close                                = 27;
    public const list_pair                                 = 28;
    public const list_delimeter                            = 29;

    public const literal_string                            = 30;
    public const literal_integer                           = 31;

    public const whitespace                                = 32;
}
