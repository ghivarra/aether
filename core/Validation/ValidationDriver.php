<?php

declare(strict_types = 1);

namespace Aether\Validation;

use Aether\Validation\Rules\ComparativeRules;
use Aether\Validation\Rules\ComparativeFieldRules;
use Aether\Validation\Rules\CreditCardRules;
use Aether\Validation\Rules\DatabaseRules;
use Aether\Validation\Rules\FormatRules;
use Aether\Exception\SystemException;
use Config\App as AppConfig;
use Aether\Language;

class ValidationDriver
{
    protected array $availableRules = [
        'comparative' => [
            'class' => ComparativeRules::class,
            'rules' => [
                'required',
                'empty',
                'exact_length',
                'not_exact_length',
                'exact_length_in',
                'not_exact_length_in',
                'max_length',
                'min_length',
                'equal',
                'not_equal',
                'in_list',
                'not_in_list',
                'greater_than',
                'greater_than_or_equal_to',
                'less_than',
                'less_than_or_equal_to',
            ]
        ],
        'comparativeField' => [
            'class' => ComparativeFieldRules::class,
            'rules' => [
                'differ',
                'match',
                'exact_length_with',
                'not_exact_length_with',
                'greater_than_field',
                'greater_than_or_equal_to_field',
                'less_than_field',
                'less_than_or_equal_to_field',
            ]
        ],
        'creditCard' => [
            'class' => CreditCardRules::class,
            'rules' => [
                'valid_cc_number'
            ]
        ],
        'database' => [
            'class' => DatabaseRules::class,
            'rules' => [
                'is_unique',
                'is_not_unique'
            ]
        ],
        'format' => [
            'class' => FormatRules::class,
            'rules' => [
                'alpha',
                'alpha_space',
                'alpha_numeric',
                'alpha_numeric_dash',
                'alpha_numeric_punct',
                'alpha_numeric_space',
                'string',
                'decimal',
                'hex',
                'integer',
                'is_natural_number',
                'is_natural_number_not_zero',
                'numeric',
                'regex_match',
                'valid_timezone',
                'valid_base64',
                'valid_json',
                'valid_email',
                'valid_emails',
                'valid_ip',
                'valid_url',
                'valid_url_strict',
                'valid_date',
            ]
        ],
    ];

    //==============================================================================================

    protected $arrayParamRules = [
        'in_list',
        'not_in_list',
        'exact_length_in',
        'not_exact_length_in',
        'valid_emails',
    ];

    //==============================================================================================

    protected array $errors = [];

    //==============================================================================================

    protected ComparativeRules|null $comparative = null;
    protected ComparativeFieldRules|null $comparativeField = null;
    protected CreditCardRules|null $creditCard = null;
    protected DatabaseRules|null $database = null;
    protected FormatRules|null $format = null;
    protected AppConfig $config;

    //==============================================================================================

    public function __construct(AppConfig $config)
    {
        $this->config = $config;
    }

    //==============================================================================================

    private function executeRule(string $rule, array $param = []): array
    {
        foreach ($this->availableRules as $key => $item):

            if (in_array($rule, $item['rules']))
            {
                if (is_null($this->$key))
                {
                    $this->$key = new $item['class']();
                }

                return [
                    'result'  => $this->$key->$rule(...$param),
                    'ruleKey' => $key,
                    'rule'    => $rule,
                    'param'   => $param,
                ];
            }

        endforeach;

        // throw if rules not found
        $message = (AETHER_ENV === 'development') ? "Rule {$rule} is not found in validation library." : "Validation failed.";
        throw new SystemException($message, 500);
    }

    //==============================================================================================

    public function execute(array $data, array $rules): void
    {
        // set default locale
        $locale   = strtolower($this->config->defaultLocale);
        $messages = Language::getFile('Validation', $locale);

        if (empty($rules))
        {            
            $errorMsg = isset($messages['noRuleSets']) ? $messages['noRuleSets'] : "Validation rule has not been set.";
            throw new SystemException($errorMsg, 500);
        }

        foreach ($data as $key => $value):

            // ignore if not in rules
            if (!isset($rules[$key]))
            {
                continue;
            }

            $methods = isset($rules[$key]['rules']) ? $rules[$key]['rules']: $rules[$key];
            $label   = isset($rules[$key]['label']) ? $rules[$key]['label'] : $key;

            // list of rules
            // explode if string or just use it if already an array
            $ruleArray = is_array($methods) ? $methods : explode('|', $methods);

            if (empty($ruleArray))
            {
                $errorMsg = isset($messages['noRuleSets']) ? $messages['noRuleSets'] : "Validation rule has not been set.";
                throw new SystemException($errorMsg, 500);
            }

            foreach ($ruleArray as $fullRule)
            {
                // get rule
                $rule = str_contains($fullRule, '[') ? strstr($fullRule, '[', true) : $fullRule;

                // push value as the first param
                $setParam = [];
                array_push($setParam, $value);
    
                // get and set param
                if (str_contains($fullRule, '['))
                {
                    $param = str_replace(['[', ']'], '', strstr($fullRule, '['));

                     // set all into set Param
                    if (!in_array($rule, $this->arrayParamRules))
                    {
                        $param = explode(',', $param);
        
                        foreach ($param as $paramItem)
                        {
                            array_push($setParam, $paramItem);
                        }
        
                    } else {
        
                        array_push($setParam, $param);
                    }
                }            
    
                // if comparative add data
                if (in_array($rule, $this->availableRules['comparativeField']))
                {
                    $try = $this->executeRule($rule, $setParam, $data);
    
                } else {
    
                    $try = $this->executeRule($rule, $setParam);
                }                    
    
                // get result
                if (!$try['result'])
                {
                    $errorLabel = $label;
                    $errorMsg   = isset($rules[$key]['error'][$rule]) ? $rules[$key]['error'][$rule] : $messages[$rule];
                    $errorParam = isset($try['param'][1]) ? $try['param'][1] : '';
                    
                    if ($try['ruleKey'] === 'comparativeField')
                    {
                        $fieldName  = isset($try['param'][1]) ? $try['param'][1] : '';
                        $errorParam = isset($rules[$fieldName]['label']) ? $rules[$fieldName]['label'] : $fieldName;
                    }
    
                    // parse
                    // create new if not exist
                    if (!isset($this->errors[$key]))
                    {
                        $this->errors[$key] = [];
                    }
    
                    $parsedMessage = str_replace(['{label}', '{param}'], [ $errorLabel, $errorParam ], $errorMsg);
                    array_push($this->errors[$key], $parsedMessage);
                }                                   
            }

        endforeach;
    }

    //==============================================================================================

    public function getStatus(): bool
    {
        return empty($this->errors);
    }

    //==============================================================================================

    public function getError(string $key): array | null
    {
        return isset($this->errors[$key]) ? $this->errors[$key] : null;
    }

    //==============================================================================================

    public function getErrors(): array
    {
        return $this->errors;
    }

    //==============================================================================================
}