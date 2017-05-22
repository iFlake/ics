<?php
class iSAXParser
{
    private $code;
    private $str_def;
    private $terminate_inline;
    private $tag_start_l;
    private $tag_start_r;
    private $tag_end_l;
    private $tag_end_r;
    private $tag_inline_l;
    private $tag_inline_r;
    private $tag_delim_cp;

    function __construct($tag_start="<its: @[command]>", $tag_end="</its>", $tag_inline="<its: @[command] />", $delim_cp=" ", $str_def = "\"'", $terminate_inline = "\n")
    {
        $this->str_def = str_split($str_def);
        $this->terminate_inline = str_split($terminate_inline);
        $s_st = explode("@[command]", $tag_start);
        $s_en = explode("@[command]", $tag_end);
        $s_in = explode("@[command]", $tag_inline);
        if (count($s_st) < 2) throw new Exception("Missing @[command] in \$tag_start");
        if (count($s_in) < 2) throw new Exception("Missing @[command] in \$tag_inline");
        $this->tag_start_l = $s_st[0];
        $this->tag_start_r = $s_st[1];
        $this->tag_end_l = $s_en[0];
        $this->tag_end_r = count($s_en) == 2 ? $s_en[1] : null;
        $this->tag_inline_l = $s_in[0];
        $this->tag_inline_r = $s_in[1];
        $this->tag_delim_cp = $delim_cp;
    }

    function Load($code)
    {
        $this->code = str_split($code);
        $this->ExpandInline();
    }

    private function ExpandInline()
    {
        $instr = null;
        $i_stage = iSAXStages_awaiting_start;
        $out = "";
        $build = "";
        $escape = false;
        for ($idx = 0; $idx < count($this->code); ++$idx)
        {
            $c = $this->code[$idx];
            if ($i_stage == iSAXStages_awaiting_start)
            {
                $out .= $c;
                if (substr(implode("", $this->code), $idx, strlen($this->tag_inline_l)) == $this->tag_inline_l)
                {
                    $out = substr($out, 0, strlen($out) - 1);
                    $idx += strlen($this->tag_inline_l) - 1;
                    $i_stage = iSAXStages_awaiting_end;
                }
            }
            else if ($i_stage == iSAXStages_awaiting_end)
            {
                $build .= $c;
                foreach ($this->terminate_inline as $terminator) if ($c == $terminator)
                {
                    $out .= $this->tag_inline_l . $build;
                    $build = "";
                    $i_stage = iSAXStages_awaiting_start;
                }
                if ($build != "")
                {
                    if ($escape == true) $escape = false;
                    else
                    {
                        if ($c == $instr) $instr = null;
                        else if ($instr == null) foreach ($this->str_def as $strop) if ($c == $strop) $instr = $c;
                        if ($c == "\\")
                        {
                            $escape = true;
                        }
                        else if ($instr == null)
                        {
                            if ($this->tag_start_l != $this->tag_inline_l || substr(implode("", $this->code), $idx, strlen($this->tag_start_r)) != $this->tag_start_r)
                            {
                                if (substr(implode("", $this->code), $idx, strlen($this->tag_inline_r)) == $this->tag_inline_r)
                                {
                                    $build = substr($build, 0, strlen($build) - 1);
                                    $idx += strlen($this->tag_inline_r) - 1;
                                    $i_stage = iSAXStages_awaiting_start;
                                    if (strpos($build, $this->tag_delim_cp) === false) $command = $build;
                                    else $command = explode($this->tag_delim_cp, $build, 2)[0];
                                    $out .= $this->tag_start_l . $build . $this->tag_start_r . $this->tag_end_l;
                                    $build = "";
                                    if ($this->tag_end_r != null) $out .= $command . $this->tag_end_r;
                                }
                            }
                            else
                            {
                                $out .= $this->tag_inline_l . $build;
                                $build = "";
                                $i_stage = iSAXStages_awaiting_start;
                            }
                        }
                    }
                }
            }
        }
        if ($build != "") $out .= $this->tag_inline_l . $build;
        $this->code = str_split($out);
    }

    function Validate()
    {

    }

    function Parse($callback)
    {
        $instr = null;
        $escape = false;
        $i_stage = iSAXStages_awaiting_start;
        $stack = [["Command" => "", "Build" => ""]];
        $out = "";
        $build = "";
        $escape = false;
        for ($idx = 0; $idx < count($this->code); ++$idx)
        {
            $c = $this->code[$idx];
            if ($i_stage == iSAXStages_awaiting_start)
            {
                $build .= $c;
                $eparsed = false;
                if ($this->tag_end_r == null)
                {
                    if (substr(implode("", $this->code), $idx, strlen($this->tag_end_l)) == $this->tag_end_l)
                    {
                        $eparsed = true;
                        $sv = array_pop($stack);
                        $command = "";
                        $parameters = "";
                        if (strpos($sv["Command"], $this->tag_delim_cp) === false) $command = $sv["Command"];
                        else
                        {
                            $exps = explode($this->tag_delim_cp, $sv["Command"], 2);
                            $command = $exps[0];
                            $parameters = $exps[1];
                        }
                        $stack[count($stack) - 1]["Build"] .= $callback($command, $parameters, $sv["Build"] . substr($build, 0, strlen($build) - 1));
                        $build = "";
                        $idx += strlen($this->tag_end_l) - 1;
                    }
                }
                else
                {
                    $sv = $stack[count($stack) - 1];
                    $command = "";
                    $parameters = "";
                    if (strpos($sv["Command"], $this->tag_delim_cp) === false) $command = $sv["Command"];
                    else
                    {
                        $exps = explode($this->tag_delim_cp, $sv["Command"], 2);
                        $command = $exps[0];
                        $parameters = $exps[1];
                    }
                    if (substr(implode("", $this->code), $idx, strlen($this->tag_end_l . $command . $this->tag_end_r)) == $this->tag_end_l . $command . $this->tag_end_r)
                    {
                        $eparsed = true;
                        $sv = array_pop($stack);
                        $stack[count($stack) - 1]["Build"] .= $callback($command, $parameters, $sv["Build"] . substr($build, 0, strlen($build) - 1));
                        $build = "";
                        $idx += strlen($this->tag_end_l . $command . $this->tag_end_r) - 1;
                    }
                }
                if ($eparsed == false && substr(implode("", $this->code), $idx, strlen($this->tag_start_l)) == $this->tag_start_l)
                {
                    $stack[count($stack) - 1]["Build"] .= substr($build, 0, strlen($build) - 1);
                    $idx += strlen($this->tag_start_l) - 1;
                    $build = "";
                    $i_stage = iSAXStages_awaiting_end;
                }
            }
            else if ($i_stage == iSAXStages_awaiting_end)
            {
                if ($c == $instr) $instr = null;
                else if ($instr == null) foreach ($this->str_def as $strop) if ($c == $strop) $instr = $c;
                $build .= $c;
                if ($escape == true) $escape = false;
                else if ($c == "\\")
                {
                    $build = substr($build, 0, strlen($build) - 1);
                    $escape = true;
                }
                else if ($instr == null)
                {
                    if (substr(implode("", $this->code), $idx, strlen($this->tag_start_r)) == $this->tag_start_r)
                    {
                        $stack[] = ["Command" => substr($build, 0, strlen($build) - strlen($this->tag_start_r)), "Build" => ""];
                        $idx += strlen($this->tag_start_r) - 1;
                        $build = "";
                        $i_stage = iSAXStages_awaiting_start;
                    }
                }
            }
        }
        $stack[count($stack) - 1]["Build"] .= $build;
        return $callback("", "", array_pop($stack)["Build"]);
    }
}
