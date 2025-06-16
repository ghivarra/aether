<?php

declare(strict_types = 1);

namespace Aether\Validation\Rules;

use Aether\Validation\Rules\BaseRules;
use \DateTime;

/**
 * This file is heavily inspired or straight up copy paste from
 * CodeIgniter 4 Validation Library
 *
 * The copyright for this library belong to:
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was in below url:
 * 
 * https://github.com/codeigniter4/CodeIgniter4/blob/develop/LICENSE
 * 
 */

class FormatRules extends BaseRules
{
    public function alpha(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return ctype_alpha($str);
    }

    //============================================================================================

    public function alpha_space(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match('/\A[A-Z ]+\z/i', $str));
    }

    //============================================================================================

    public function alpha_numeric(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return ctype_alnum($str);
    }

    //============================================================================================

    public function alpha_numeric_dash(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return preg_match('/\A[a-z0-9_-]+\z/i', $str) === 1;
    }

    //============================================================================================

    public function alpha_numeric_punct(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return preg_match('/\A[A-Z0-9 ~!#$%\&\*\-_+=|:.]+\z/i', $str) === 1;
    }

    //============================================================================================

    public function alpha_numeric_space(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match('/\A[A-Z0-9 ]+\z/i', $str));
    }

    //============================================================================================

    public function string(string|int|float|null $str = null): bool
    {
        return is_string($str);
    }

    //============================================================================================

    public function decimal(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match('/\A[-+]?\d{0,}\.?\d+\z/', $str));
    }

    //============================================================================================

    public function hex(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return ctype_xdigit($str);
    }

    //============================================================================================

    public function integer(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match('/\A[\-+]?\d+\z/', $str));
    }

    //============================================================================================

    public function is_natural_number(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return ctype_digit($str);
    }

    //============================================================================================

    public function is_natural_number_no_zero(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return ($str !== '0' && ctype_digit($str));
    }

    //============================================================================================

    public function numeric(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match('/\A[\-+]?\d*\.?\d+\z/', $str));
    }

    //============================================================================================

    public function regex_match(string|int|float|null $str = null, string $pattern): bool
    {
        $str = $this->toString($str);

        return boolval(preg_match($pattern, $str));
    }

    //============================================================================================

    public function valid_timezone(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return in_array($str, timezone_identifiers_list(), true);
    }

    //============================================================================================

    public function valid_base64(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        return base64_encode(base64_decode($str, true)) === $str;
    }

    //============================================================================================

    public function valid_json(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        json_decode($str);

        return json_last_error() === JSON_ERROR_NONE;
    }

    //============================================================================================

    public function valid_email(string|int|float|null $str = null): bool
    {
        $str = $this->toString($str);

        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str, $matches))
        {
            $str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
        }

        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }

    //============================================================================================

    public function valid_emails(array|null $str = null): bool
    {
        foreach ($str as $email):

            $email = $this->toString($email);

            if (!$this->valid_email($email))
            {
                return false;   
            }

        endforeach;

        return true;
    }

    //============================================================================================

    public function valid_ip(string|null $ip = null, string $option = 'ipv4'): bool
    {
        if (empty($ip) || is_null($ip))
        {
            return false;
        }

        $list = [
            'ipv4' => FILTER_FLAG_IPV4,
            'ipv6' => FILTER_FLAG_IPV6,
        ];

        return filter_var($ip, FILTER_VALIDATE_IP, $list[$option]) !== false;
    }

    //============================================================================================

    public function valid_url(string|int|float|null $str = null): bool
    {
        if (empty($str) || is_null($str))
        {
            return false;
        }

        $str = $this->toString($str);

        // add http or substring it if not exist
        if (preg_match('/\A(?:([^:]*)\:)?\/\/(.+)\z/', $str, $matches))
        {
            if (! in_array($matches[1], ['http', 'https'], true)) {
                return false;
            }

            $str = $matches[2];
        }

        $str = 'http://' . $str;

        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    //============================================================================================

    public function valid_url_strict(string|int|float|null $str = null): bool
    {
        if (empty($str) || is_null($str))
        {
            return false;
        }

        $str = $this->toString($str);

        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    //============================================================================================

    public function valid_date(string|null $str = null, string|null $format = null): bool
    {
        if (empty($str) || is_null($str))
        {
            return false;
        }

        $str = $this->toString($str);

        if (empty($format) || is_null($format))
        {
            return strtotime($str) !== false;
        }

        $date   = DateTime::createFromFormat($format, $str);
        $errors = DateTime::getLastErrors();

        if ($date === false)
        {
            return false;
        }

        // PHP 8.2 or later.
        if ($errors === false)
        {
            return true;
        }

        return $errors['warning_count'] === 0 && $errors['error_count'] === 0;
    }

    //============================================================================================
}