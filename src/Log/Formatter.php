<?php

namespace Illuminated\Console\Log;

use Monolog\Formatter\LineFormatter;

class Formatter extends LineFormatter
{
    public function __construct()
    {
        parent::__construct("[%datetime%]: [%level_name%]: %message%\n%context%\n", null, true, true);
    }

    public function format(array $record)
    {
        $output = parent::format($record);
        return rtrim($output) . "\n";
    }

    protected function convertToString($data)
    {
        $decoded = is_json($data, true);
        if ($decoded) {
            $data = $decoded;
        }

        if (is_string($data)) {
            return $data;
        }

        return var_export($data, true);
    }
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function is_json($string, $return = false)
{
    if (!is_string($string)) {
        return false;
    }

    $data = json_decode($string);
    if (json_last_error() != JSON_ERROR_NONE) {
        return false;
    }

    return ($return ? $data : true);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
