<?php

class Template {
    public $assignedValues = array();
    public $tpl;
    
    function __construct($filename = "") {
        if (!empty($filename)) {
            if (file_exists($filename)) {
                $this -> tpl = file_get_contents($filename);
            } else {
                exit("ERROR: template file not found!");
            }
        }
    }
    
    // Finds the variables and assigns the correct variable value
    function assign($searchFor, $replaceWith) {
        if (!empty($searchFor)) {
            $this -> assignedValues[strtoupper($searchFor)] = $replaceWith;
        }
    }

    function render() {
        // Parse conditional blocks before rendering
        $this->tpl = $this->parseIfCondtionBlocks($this->tpl);
    
        // Replace placeholders with assigned values
        foreach ($this->assignedValues as $key => $value) {
            if (is_array($value)) {
                $value = implode('', $value);
            } else {
                // If the value is not an array, directly replace the placeholder
                $this->tpl = str_replace("{" . strtoupper($key) . "}", $value, $this->tpl);
            }
        }
    
        return $this->tpl;
    }

    // Finds if statements and implements the if statement logic
    function parseIfCondtionBlocks($content) {
        // IF statement pattern
        $pattern = '/\{IF\s+(.*?)\}(.*?)\{ENDIF\}/s';
    
        $content = preg_replace_callback($pattern, function($matches) {
            $condition = $matches[1];
            $blockContent = $matches[2];
    
            $conditionResult = $this->evaluateCondition($condition);
    
            return $conditionResult ? $blockContent : '';
        }, $content);    
        return $content;
    }    
    

    // Evaluates the overall condition
    function evaluateCondition($condition) {
        $lowercaseCondition = strtolower($condition);

        if (preg_match('/^(.*?)\s*===\s*(.*?)$/', $lowercaseCondition, $matches)) {
            $leftOperand = trim($matches[1]);
            $rightOperand = trim($matches[2]);
            $leftResult = $this->evaluateSingleCondition($leftOperand);
            $rightResult = $this->evaluateSingleCondition($rightOperand);
            return $leftResult === $rightResult;
        }
        
        return $condition;
    }
    
    // Evaluates individual conditions or operands
    function evaluateSingleCondition($condition) {
        if ($condition === 'true' || $condition === 'false') {
            return $condition === 'true';
        }

        if (strpos($condition, '!empty(') === 0) {
            $expression = substr($condition, 7, -1);
            return !empty(eval("return $expression;"));
        }
    
        if (strpos($condition, 'empty(') === 0) {
            $expression = substr($condition, 6, -1);
            return empty(eval("return $expression;"));
        }

        if (isset($this->assignedValues[strtoupper($condition)])) {
            return !empty($this->assignedValues[strtoupper($condition)]);
        }
        
        return !empty($condition);
    }
}
?>
