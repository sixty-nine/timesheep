<?php

namespace SixtyNine\Timesheep\Helper;

use Webmozart\Assert\Assert;

class Arrays
{
    public static function detectDimension($arr, $count = 0): int
    {
        if (is_array($arr)) {
            $val = array_values($arr);
            $val = reset($val);
            return self::detectDimension($val, $count + 1);
        }

        return $count;
    }

    public static function withouth(array $data, array $excludedKeys = []): array
    {
        $res = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $excludedKeys, false)) {
                continue;
            }
            $res[$key] = $value;
        }
        return $res;
    }

    public static function setValue(array &$data, array $indexes, $value): void
    {
        $last = end($indexes);
        $parent = &$data;

        foreach ($indexes as $idx) {
            $isLastLevel = $idx === $last;

            if (!$isLastLevel) {
                if (!isset($parent[$idx])) {
                    $parent[$idx] = [];
                }

                $parent = &$parent[$idx];
                continue;
            }

            $parent[$idx] = $value;
        }
    }

    public static function getValue(array $data, array $indexes)
    {
        $curItem = $data;
        foreach ($indexes as $idx) {
            if (!isset($curItem[$idx])) {
                return null;
            }
            $curItem = $curItem[$idx];
        }
        return $curItem;
    }

    public static function setValueDotted(array &$data, string $key, $value): void
    {
        self::setValue($data, explode('.', $key), $value);
    }

    public static function getValueDotted(array $data, string $key)
    {
        return self::getValue($data, explode('.', $key));
    }

    public static function isPermutation(array $a1, array $a2): bool
    {
        if (count($a1) !== count($a2)) {
            return false;
        }

        return [] === array_diff($a1, $a2);
    }

    public static function getPermutations(array $a1, array $a2): array
    {
        Assert::true(self::isPermutation($a1, $a2), 'Not a permutation');

        $permutations = [];
        foreach ($a2 as $value) {
            $idx = array_search($value, $a1, false);
            if (false === $idx) {
                throw new \RuntimeException('Not a permutation');
            }
            $permutations[] = $idx;
        }
        return $permutations;
    }

    /**
     * https://stackoverflow.com/a/173479/643106
     *
     * Be careful, there are lot of edge-cases where this will not work properly.
     *
     * @param  array $arr
     * @return bool
     */
    public static function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
