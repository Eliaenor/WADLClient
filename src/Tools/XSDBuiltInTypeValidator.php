<?php

namespace WADLClient\Tools;


class XSDBuiltInTypeValidator
{
    /**
     * isXsdString match the String format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#string for more information.
     *
     * @param $string
     * @return bool
     */
    static public function isXsdString($string)
    {
        return is_string($string);
    }

    /**
     * isXsdBoolean match the Boolean format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#boolean for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdBoolean(string $var): bool
    {
        return is_bool($var);
    }

    /**
     * isXsdDecimal match the Decimal format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#decimal for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdDecimal(string $var): bool
    {
        return is_numeric($var);
    }

    /**
     * isXsdFloat match the Float format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#float for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdFloat(string $var): bool
    {
        return is_float($var);
    }

    /**
     * isXsdDouble match the Double format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#double for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdDouble(string $var): bool
    {
        return is_double($var);
    }

    /**
     * isXsdDuration match the Duration format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#duration for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdDuration(string $var): bool
    {
        return preg_match('^(-)?P(?!$)(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+(.\d+)?S)?)?$', $var);
    }

    /**
     * isXsdDateTime match the DateTime format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#dateTime for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdDateTime(string $var): bool
    {
        $regExp = '/^-?(?!0{4,}\b)(\d{4,})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(.\d+)?(Z|[+-]\d{2}:\d{2})$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        $date[1] = self::handleLeapYear($date[1]);
        if (!checkdate($date[2], $date[3], $date[1])
            || ($date[4] == 24 && $date[5] != 00 || $date[6] != 00)
            || $date[4] >= 24 || $date[5] >= 60 || $date[6] >= 60) {
            return false;
        }

        unset($date[0]);
        $format = $date[7] === '' ? 'YmdHisP' : 'YmdHis.uP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdTime match the Time format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#time for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdTime(string $var): bool
    {
        $regExp = '/^(\d{2}):(\d{2}):(\d{2})(.\d+)?(Z|[+-]\d{2}:\d{2})$/';

        if (!preg_match($regExp, $var, $time)) {
            return false;
        }

        if (($time[1] == 24 && $time[2] != 00 || $time[3] != 00) ||
            $time[1] > 24 || $time[2] >= 60 || $time[3] >= 60) {
            return false;
        }

        unset($time[0]);
        $format = $time[4] === '' ? 'HisP' : 'His.uP';
        $dateString = str_replace('Z', '+00:00', implode('', $time));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdDate match the Date format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#date for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdDate(string $var): bool
    {
        $regExp = '/^-?(?!0{4,}\b)(\d{4,})-(\d{2})-(\d{2})(Z|[+-]\d{2}:\d{2})?$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        $date[1] = self::handleLeapYear($date[1]);
        if (!checkdate($date[2], $date[3], $date[1])) {
            return false;
        }

        unset($date[0]);
        $format = $date[4] === '' ? 'Ymd' : 'YmdP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdGYearMonth match the GYearMonth format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#gYearMonth for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdGYearMonth(string $var): bool
    {
        $regExp = '/^-?(?!0{4,}\b)(\d{4,})-(\d{2})(Z|[+-]\d{2}:\d{2})?$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        $date[1] = self::handleLeapYear($date[1]);
        if ($date[2] > 12) {
            return false;
        }

        unset($date[0]);
        $format = $date[3] === '' ? 'Ym' : 'YmP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdGYear match the GYear format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#gYear for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdGYear(string $var): bool
    {
        $regExp = '/^-?(?!0{4,}\b)(\d{4,})(Z|[+-]\d{2}:\d{2})?$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        $date[1] = self::handleLeapYear($date[1]);

        unset($date[0]);
        $format = $date[2] === '' ? 'Y' : 'YP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdGMonthDay match the GMonthDay format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#gMonthDay for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdGMonthDay(string $var): bool
    {
        $regExp = '/^--(\d{2})-(\d{2})(Z|[+-]\d{2}:\d{2})$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        if ($date[1] > 12) {
            return false;
        }

        unset($date[0]);
        $format = $date[3] === '' ? 'md' : 'mdP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdGDay match the GDay format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#gDay for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdGDay(string $var): bool
    {
        $regExp = '/^---(\d{2})(Z|[+-]\d{2}:\d{2})$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }

        if ($date[1] > 31) {
            return false;
        }

        unset($date[0]);
        $format = $date[2] === '' ? 'd' : 'dP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isXsdGMonth match the GMonth format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#gMonth for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isXsdGMonth(string $var): bool
    {
        $regExp = '/^--(\d{2})(Z|[+-]\d{2}:\d{2})$/';

        if (!preg_match($regExp, $var, $date)) {
            return false;
        }
        if ($date[1] > 12) {
            return false;
        }

        unset($date[0]);
        $format = $date[2] === '' ? 'm' : 'mP';
        $dateString = str_replace('Z', '+00:00', implode('', $date));

        return is_object(\DateTime::createFromFormat($format, $dateString));
    }

    /**
     * isHexBinary match the HexBinary format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#hexBinary for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isHexBinary(string $var): bool
    {
        $regExp = '/^[0-9A-F]{2,}$/';

        return  strlen($var)% 2 == 0 || !preg_match($regExp, $var);
    }

    /**
     * isBase64Binary match the Base64Binary format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#base64Binary for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isBase64Binary(string $var): bool
    {
        $regExp = '/^^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)$/';

        return strlen($var)% 4 == 0 ||!preg_match($regExp, $var);
    }

    /**
     * isAnyUri match the AnyUri format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#unyUrifor more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isAnyUri(string $var): bool
    {
        return is_string($var);
    }

    /**
     * isQName match the QName format as described in the XMLSchema documentation.
     * See https://www.w3.org/TR/xmlschema-2/#qName for more information.
     *
     * @param string $var
     * @return bool
     */
    static public function isQName(string $var): bool
    {
        return is_string($var);
    }

    /**
     * Handling of leap years since checkdate() handle only years from 1 to 32767 included.
     * Checking if the year is a Leap year then setting it to arbitrary selected year 2000 for leap year and
     * 1900 for non-Leap year.
     *
     * The rule used to know if it is a Leap year is the following.
     * It is a Leap year when the year number is neither :
     *  - a multiplier of 400
     *  OR
     *  - a multiplier of 4 but not of 100
     */
    static private function handleLeapYear(string $year)
    {
        return $year % 400 == 0 | ($year % 4 == 0 && $year % 100 != 0) ? 2000 : 1900;
    }
}