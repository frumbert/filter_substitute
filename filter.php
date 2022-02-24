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

defined('MOODLE_INTERNAL') || die();

class filter_substitute extends moodle_text_filter {

    public function filter($text, array $options = array()) {
        global $CFG, $COURSE;

        for ( $i = 0 ; $i < 16; $i++ ) {

            $find = trim(get_config('filter_substitute', 'find_' . $i));
            if (!empty($find)) {
                $replace = trim(get_config('filter_substitute', 'replace_' . $i));
                if (!empty($replace)) {
                    $text = str_replace($find, $replace, $text);
                    $text = self::replace_internals($text);
                }
            }

        }

        return $text;
    }

    private static function replace_internals($text) {
        global $USER, $COURSE;
        $find = array_map(function($n){return "%%{$n}%%";}, ['COURSE:ID','COURSE:FULLNAME','COURSE:SHORTNAME','COURSE:IDNUMBER','USER:ID','USER:FIRSTNAME','USER:LASTNAME','USER:EMAIL','USER:USERNAME','USER:INSTITUTION','USER:DEPARTMENT']);
        $repl = [$COURSE->id, $COURSE->fullname, $COURSE->shortname, $COURSE->idnumber, $USER->id, $USER->firstname, $USER->lastname, $USER->email, $USER->username, $USER->institution, $USER->department];
        return str_replace($find, $repl, $text);
    }
}