<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreditCardCheckerController extends Controller
{
    private $_rules = [];
    public function __construct() {
        $this->rules();
    }
    public function rules()
    {
        $this->_rules = [
            [
                'network' => 'American Express',
                'rules' => [
                    'length' => [15],
                    'ranges' => [34, 37]
                ]
            ], [
                'network' => 'Diners Club International',
                'rules' => [
                    'length' => [14],
                    'ranges' => [36]
                ]
            ], [
                'network' => 'Visa',
                'rules' => [
                    'length' => [13, 16],
                    'ranges' => [4]
                ]
            ], [
                'network' => 'Visa Electron',
                'rules' => [
                    'length' => [16],
                    'ranges' => [4026, 417500, 4405, 4508, 4844, 4913, 4917]
                ]
            ], [
                'network' => 'Discover Card',
                'rules' => [
                    'length' => [16],
                    'ranges' => array_merge([6011, 65], $this->setRange(622126, 622925), $this->setRange(644, 649))
                ]
            ]
        ];
    }
    public function creditCardCheck()
    {
        $test_cases = array();
        $test_cases["36035390282568"] = "Diners Club International";
        $test_cases["3603777745390282568"] = "Invalid";
        $test_cases["346416800707698"] = "American Express";
        $test_cases["34"] = "Invalid";
        $test_cases[""] = "Invalid";
        $test_cases["3464 1680 070 7699"] = "American Express";
        $test_cases["3464-1680-070-7697"] = "American Express";
        $test_cases[346416800707998] = "American Express";
        $test_cases["366416800707698"] = "Invalid";
        $test_cases["36641680asfadf"] = "Invalid";
        $test_cases["4123456789012"] = "Visa";
        $test_cases["4123456789012345"] = "Visa";
        $test_cases["4026123412341234"] = "Visa Electron";
        $test_cases["4175001234123412"] = "Visa Electron";
        $test_cases["4175011234123412"] = "Visa";
        $test_cases["400000000000000"] = "Invalid";
        $test_cases["4"] = "Invalid";
        $test_cases["491700"] = "Invalid";
        $failures = 0; 
        foreach($test_cases as $key => $value) {
            $issuer = $this->find($key);
            if ($issuer != $value) {
                $failures += 1;
                echo "Failure for case: ", $this->quoted($key), "\n";
                echo " Expected: ", $this->quoted($value), "\n";
                echo " Actual: ", $this->quoted($issuer), "\n";
                echo "\n";
            }
        }
        printf("%d tests, %d successes, %d failures", count($test_cases), count($test_cases)-$failures, $failures);
    }
    public function find($key)
    {
        $cleanKey = (int)str_replace(' ', '', (str_replace('-', '', $key)));
        $value = 'Invalid';
        foreach ($this->_rules as $key => $rule) {
            foreach ($rule['rules']['ranges'] as $prefix) {
                if (str_starts_with($cleanKey, $prefix) && in_array(strlen($cleanKey), $rule['rules']['length'])) {
                    $value = $rule['network'];
                } 
            }
        }
        return $value;
    }
    public function quoted($issuer)
    {
        return "'" . $issuer . "'";
    }
    public function setRange($from, $to)
    {
        $newRange = [];
        while ($from <= $to) {
            $newRange[] = $from++;
        }
        return $newRange;
    }
}
