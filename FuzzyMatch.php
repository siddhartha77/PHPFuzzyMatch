<?php

class FuzzyMatch {
    private const MAX_LEVENSHTEIN_DISTANCE = 2;

    private const WEIGHT_PERFECT = 1000;
    private const WEIGHT_LEFT = 900;
    private const WEIGHT_PERFECT_LONGEST_SUBSTR = 60;
    private const WEIGHT_PERFECT_SUBSTR = 45;
    private const WEIGHT_LEFT_SUBSTR = 40;
    private const WEIGHT_LEVEN = 20;
    private const WEIGHT_LEVEN_SUBSTR = 10;

    public static function Match(array $haystack_array, string $needle, int $limit) : array {
        $results = [];
        $needle_name = strtoupper($needle);

        foreach ($haystack_array as $candidate) {
            $candidate_name = strtoupper($candidate['name']);
            $matched = false;
            $weight = 0;

            if ($candidate_name == $needle_name) {
                /* Perfect match */
                $weight = self::WEIGHT_PERFECT;
                $matched = true;
            } elseif (substr($candidate_name, 0, strlen($needle_name)) == $needle_name) {
                /* Left portion matches */
                $weight = self::WEIGHT_LEFT;
                $matched = true;
            } else {
                /*
                 * We don't break out of this loop because subsequent substring might yield a higher weight than others.
                 * This is why we do the weight conditionals.
                 */

                $needle_array = preg_split('/\s+/', $needle_name);

                /* Sort the array with the longest word first */
                usort($needle_array, function($a, $b) {
                   return strlen($b) <=> strlen($a);
                });

                /* Test each substring */
                foreach ($needle_array as $key=>$needle_substr) {
                    foreach (preg_split('/\s+/', $candidate_name) as $candidate_substr) {
                        if ($candidate_substr == $needle_substr) {
                            /* Substring matches */
                            if ($key == 0) {
                                /* If this is the longest word, then it is weighted higher */
                                $weight += self::WEIGHT_PERFECT_LONGEST_SUBSTR;
                            } else {
                                $weight += self::WEIGHT_PERFECT_SUBSTR;
                            }

                            $matched = true;
                        } elseif (substr($candidate_substr, 0, strlen($needle_substr)) == $needle_substr) {
                            /* Substring left portion matches */
                            $weight += self::WEIGHT_LEFT_SUBSTR;
                            $matched = true;
                        }

                        $substr_distance = levenshtein($candidate_substr, $needle_substr);

                        if ($substr_distance <= self::MAX_LEVENSHTEIN_DISTANCE) {
                            /* Levenshtein distance of substring is within limits */
                            $weight += self::WEIGHT_LEVEN_SUBSTR - $substr_distance;
                            $matched = true;
                        }
                    }
                }
            }

            if ($weight < self::WEIGHT_LEVEN ) {
                $distance = levenshtein($candidate_name, $needle_name);

                if ($distance <= self::MAX_LEVENSHTEIN_DISTANCE) {
                    /* Levenshtein distance of candidate is within limits */
                    $weight = self::WEIGHT_LEVEN - $distance;
                    $matched = true;
                }
            }

            if ($matched) {
                $candidate['weight'] = $weight;
                $results[] = $candidate;
            }
        }

        /* Sort by weight, descending */
        usort($results, function(array $a, array $b) {
            return $b['weight'] <=> $a['weight'];
        });

        /* Limit the array to $limit entries */
        return array_slice($results, 0, $limit);
    }
}