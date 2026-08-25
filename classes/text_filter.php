<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    filter
 * @subpackage substitute
 * @copyright  tim@avideelearning.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace filter_substitute;

defined('MOODLE_INTERNAL') || die();

class text_filter extends \core_filters\text_filter {

    /**
     * Replace plain and URL-encoded token variants.
     *
     * Plain format is %%TOKEN%% and encoded format is ##TOKEN##.
     *
     * @param string $text
     * @param array $values token => value map
     * @return string
     */
    private static function replace_token_values(string $text, array $values): string {
        if (empty($values)) {
            return $text;
        }

        $plain = [];
        $encoded = [];
        foreach ($values as $token => $value) {
            $stringvalue = (string)$value;
            $plain['%%' . $token . '%%'] = $stringvalue;

            // Trailing whitespace is hard to spot in plain text but becomes visible as %20 in URLs.
            $encoded['##' . $token . '##'] = rawurlencode(rtrim($stringvalue));
        }

        return strtr($text, $plain + $encoded);
    }

    // currently support 16 replacements
    #[\Override]
    public function filter($text, array $options = array()) {

        if (empty($text) || is_numeric($text)) {
            return $text;
        }

        // process user replacements
        for ( $i = 0 ; $i < 16; $i++ ) {
            $find = trim(get_config('filter_substitute', 'find_' . $i));
            if (!empty($find)) {
                $replace = trim(get_config('filter_substitute', 'replace_' . $i));
                if (!empty($replace)) {
                    $text = str_replace($find, $replace, $text);
                }
            }
        }
        // then internal known values
        $text = self::replace_internals($text);

        return $text;
    }

    // we have some hard coded replacments we can find
    // in the unlikely format %%AREA:VARIABLE%% (case-sensitive)
    private static function replace_internals($text) {
        global $USER, $COURSE, $PAGE;
        $cmid = @$PAGE->cm->id; // ignore if not set
        $modname = @$PAGE->cm->modname; // ignore if not set
        $tokenvalues = [
            'PAGE:CONTEXTID',
            'PAGE:CMID',
            'PAGE:MODULE',

            'COURSE:ID',
            'COURSE:FULLNAME',
            'COURSE:SHORTNAME',
            'COURSE:IDNUMBER',

            'USER:ID',
            'USER:FIRSTNAME',
            'USER:LASTNAME',
            'USER:EMAIL',
            'USER:USERNAME',
            'USER:INSTITUTION',
            'USER:DEPARTMENT',

            'SESSION:KEY'
        ];
        $tokenreplacements = [
            $PAGE->context->id,
            $cmid ?? 0,
            $modname ?? '',

            $COURSE->id,
            $COURSE->fullname,
            $COURSE->shortname,
            $COURSE->idnumber,

            $USER->id ?? 0,
            $USER->firstname ?? '',
            $USER->lastname ?? '',
            $USER->email ?? '',
            $USER->username ?? '',
            $USER->institution ?? '',
            $USER->department ?? '',

            sesskey()
        ];
        $text = self::replace_token_values($text, array_combine($tokenvalues, $tokenreplacements));

        // Replace course custom fields using %%COURSE:FIELD:<shortname>%% and ##COURSE:FIELD:<shortname>##.
        if (preg_match_all('/(%%|##)COURSE:FIELD:([^%#]+)\\1/', $text, $matches)) {
            $requestedshortnames = array_unique($matches[2]);
            $requestedlookup = array_fill_keys($requestedshortnames, true);
            $replacements = [];

            $customfields = \core_course\customfield\course_handler::create()->get_instance_data($COURSE->id, true);
            foreach ($customfields as $field) {
                $fd = new \core_customfield\output\field_data($field);
                $shortname = $fd->get_shortname();
                if (!isset($requestedlookup[$shortname])) {
                    continue;
                }
                $replacements['COURSE:FIELD:' . $shortname] = $fd->get_value();
            }

            $text = self::replace_token_values($text, $replacements);
        }

        if (preg_match_all('/(%%|##)PREF:([^%#]*)\\1/', $text, $prefs)) {
            $prefreplacements = [];
            foreach (array_unique($prefs[2]) as $pref) {
                $prefreplacements['PREF:' . $pref] = get_user_preferences($pref, '', $USER);
            }
            $text = self::replace_token_values($text, $prefreplacements);
        }

        return $text;

        // return self::mb_str_replace($find, $repl, $text);
    }

    // private static function mb_str_replace($needle, $replacement, $haystack) {
    //     return implode($replacement, mb_split($needle, $haystack));
    // }
}