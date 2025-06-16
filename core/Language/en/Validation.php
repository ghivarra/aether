<?php 

return [
    // Core Messages
    'noRuleSets' => 'No rule sets specified in Validation configuration.',

    // default error message
    'default' => 'The {label} field is not valid.',

    // comparative rule error message
    'required'                 => 'The {label} field is required.',
    'empty'                    => 'The {label} field must be empty.',
    'exact_length'             => 'The {label} field must be exactly {param} in length.',
    'not_exact_length'         => 'The {label} field must not be {param} in length.',
    'exact_length_in'          => 'The {label} field length must be one of: {param}',
    'not_exact_length_in'      => 'The {label} field length must not be one of: {param}',
    'max_length'               => 'The {label} field cannot exceed {param} characters.',
    'min_length'               => 'The {label} field must be at least {param} characters.',
    'equal'                    => 'The {label} field must be exactly: {param}.',
    'not_equal'                => 'The {label} field must not be {param}.',
    'in_list'                  => 'The {label} field must be one of: {param}.',
    'not_in_list'              => 'The {label} field must not be one of: {param}.',
    'greater_than'             => 'The {label} field must be greater than {param}.',
    'greater_than_or_equal_to' => 'The {label} field must be greater than or equal to {param}.',
    'less_than'                => 'The {label} field must be less than {param}.',
    'less_than_or_equal_to'    => 'The {label} field must be less than or equal to {param}.',

    // comparative field error message
    'differ'                         => 'The {label} field must be different with the {param} field.',
    'match'                          => 'The {label} field must be matched with the {param} field.',
    'exact_length_with'              => 'The {label} field must have the same exact length with the {param} field.',
    'not_exact_length_with'          => 'The {label} field must not have the same exact length with the {param} field.',
    'greater_than_field'             => 'The {label} field value must be greater than the {param} field value.',
    'greater_than_or_equal_to_field' => 'The {label} field value must be greater than or equal to the {param} field value.',
    'less_than_field'                => 'The {label} field value must be less than the {param} field value.',
    'less_than_or_equal_to_field'    => 'The {label} field value must be less than or equal to the {param} field value.',

    // credit card error message
    'valid_cc_number' => '{label} does not appear to be a valid credit card number.',

    // database error message
    'is_unique'     => 'The {label} field must contain a unique value.',
    'is_not_unique' => 'The {label} field must contain an existing value.',

    // format error message
    'alpha'                      => 'The {label} field must only contain alphabetical characters.',
    'alpha_space'                => 'The {label} field must only contain alphabetical characters and space.',
    'alpha_numeric'              => 'The {label} field must only contain alphanumerical characters.',
    'alpha_numeric_dash'         => 'The {label} field must only contain alphanumerical characters, underscore, and dash.',
    'alpha_numeric_punct'        => 'The {label} field must only contain alphanumerical characters, underscore, dash, and ~ ! # $ % & * - _ + = | : . characters.',
    'alpha_numeric_space'        => 'The {label} field must only contain alphanumerical characters and space.',
    'string'                     => 'The {label} field should be a string.',
    'decimal'                    => 'The {label} field should be in decimal.',
    'hex'                        => 'The {label} field should be in hex.',
    'integer'                    => 'The {label} field should be an integer.',
    'is_natural_number'          => 'The {label} field should be a natural number.',
    'is_natural_number_not_zero' => 'The {label} field should be a natural number without zero.',
    'numeric'                    => 'The {label} field should be a number.',
    'regex_match'                => '{label} is not valid.',
    'valid_timezone'             => '{label} is not a valid timezone.',
    'valid_base64'               => '{label} is not a valid base64.',
    'valid_json'                 => '{label} is not a valid JSON.',
    'valid_email'                => '{label} is not a valid email.',
    'valid_emails'               => 'One of the {label} is not a valid email.',
    'valid_ip'                   => '{label} is not a valid IP Address.',
    'valid_url'                  => '{label} is not a valid URL.',
    'valid_url_strict'           => '{label} is not a valid URL.',
    'valid_date'                 => '{label} is not a valid date.',
];