<?php

namespace Transitive\Utils;

use DateTime;

abstract class Strings
{
    /**
     * Returns an string clean of UTF8 characters. It will convert them to a similar ASCII character
     * www.unexpectedit.com.
     */
    public static function cleanString($text) {
        // 1) convert á ô => a o
        $text = preg_replace('/[áàâãªä]/u', 'a', $text);
        $text = preg_replace('/[ÁÀÂÃÄ]/u', 'A', $text);
        $text = preg_replace('/[ÍÌÎÏ]/u', 'I', $text);
        $text = preg_replace('/[íìîï]/u', 'i', $text);
        $text = preg_replace('/[éèêë]/u', 'e', $text);
        $text = preg_replace('/[ÉÈÊË]/u', 'E', $text);
        $text = preg_replace('/[óòôõºö]/u', 'o', $text);
        $text = preg_replace('/[ÓÒÔÕÖ]/u', 'O', $text);
        $text = preg_replace('/[úùûü]/u', 'u', $text);
        $text = preg_replace('/[ÚÙÛÜ]/u', 'U', $text);
        $text = preg_replace('/[’‘‹›‚]/u', "'", $text);
        $text = preg_replace('/[“”«»„]/u', '"', $text);
        $text = str_replace('–', '-', $text);
        //$text = str_replace(' ',' ',$text);
        $text = str_replace('ç', 'c', $text);
        $text = str_replace('Ç', 'C', $text);
        $text = str_replace('ñ', 'n', $text);
        $text = str_replace('Ñ', 'N', $text);

        //2) Translation CP1252. &ndash; => -
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
        $trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
        $trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
        $trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
        $trans[chr(134)] = '&dagger;';    // Dagger
        $trans[chr(135)] = '&Dagger;';    // Double Dagger
        $trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
        $trans[chr(137)] = '&permil;';    // Per Mille Sign
        $trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
        $trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
        $trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
        $trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
        $trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
        $trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
        $trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
        $trans[chr(149)] = '&bull;';    // Bullet
        $trans[chr(150)] = '&ndash;';    // En Dash
        $trans[chr(151)] = '&mdash;';    // Em Dash
        $trans[chr(152)] = '&tilde;';    // Small Tilde
        $trans[chr(153)] = '&trade;';    // Trade Mark Sign
        $trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
        $trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
        $trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
        $trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
        $trans['euro'] = '&euro;';    // euro currency symbol
        ksort($trans);

        foreach ($trans as $k => $v) {
            $text = str_replace($v, $k, $text);
        }

        // 3) remove <p>, <br/> ...
        $text = strip_tags($text);

        // 4) &amp; => & &quot; => '
        $text = html_entity_decode($text);

        // 5) remove Windows-1252 symbols like "TradeMark", "Euro"...
        $text = preg_replace('/[^(\x20-\x7F)]*/', '', $text);

        $targets = array('\r\n', '\n', '\r', '\t');
        $results = array(' ', ' ', ' ', '');
        $text = str_replace($targets, $results, $text);

        //XML compatible
        /*
        $text = str_replace("&", "and", $text);
        $text = str_replace("<", ".", $text);
        $text = str_replace(">", ".", $text);
        $text = str_replace("\\", "-", $text);
        $text = str_replace("/", "-", $text);
        */
        return $text;
    }

    public static function post_slug(string $str): string {
        return strtolower(preg_replace(array('#[\\s-]+#', '#[^A-Za-z0-9\. _]+#'), array('_', ''), self::cleanString(urldecode($str))));
    }

    public static function startsWith(string $haystack, string $needle): string
    {
        // search backwards starting from haystack length characters from the end
        return '' === $needle || false !== strrpos($haystack, $needle, -strlen($haystack));
    }

    public static function endsWith(string $haystack, string $needle): string
    {
        // search forward starting from end minus needle length characters
        return '' === $needle || (($temp = strlen($haystack) - strlen($needle)) >= 0 && false !== strpos($haystack, $needle, $temp));
    }

    public static function contentEditableParse(string $content): string
    {
        $content = str_replace('<br>', '<br />', trim($content));
        $content = str_replace('<div><br />', '<br /><br />', $content);
        $content = str_replace(array('<div>', '</div>'), '', $content);

        preg_match_all('/<div[^>]+>(.*?)<\\/div>/m', $content, $matches);

        preg_match('/^(<br \\/>)+$/', $content, $matches);

        if($matches)
            $content = '';

        return $content;
    }

    public static function random(int $length = 8): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:?';
        $password = substr(str_shuffle($chars), 0, $length);

        return $password;
    }

    //http://php.net/manual/fr/dateinterval.format.php#96768
    public static function formatDateDiff(DateTime $start, $end = null): string
    {
        if(!($start instanceof DateTime)) {
            $start = new DateTime($start);
        }

        if(null === $end) {
            $end = new DateTime();
        }
        if(!($end instanceof DateTime)) {
            $end = new DateTime($start);
        }

        $interval = $end->diff($start);
        $doPlural = function ($nb, $str) {return $nb > 1 ? $str.'s' : $str; }; // adds plurals

        $format = array();
        if(0 !== $interval->y) {
            $format[] = '%y '.self::pluralize($interval->y, 'an');
        }
        if(0 !== $interval->m) {
            $format[] = '%m mois';
        }
        if(0 !== $interval->d) {
            $format[] = '%d '.self::pluralize($interval->d, 'jour');
        }
        if(0 !== $interval->h) {
            $format[] = '%h '.self::pluralize($interval->h, 'heure');
        }
        if(0 !== $interval->i) {
            $format[] = '%i '.self::pluralize($interval->i, 'minute');
        }
        if(0 !== $interval->s) {
            if(!count($format)) {
                return 'Il y a moins d\'une minute';
            } else {
                $format[] = '%s '.self::pluralize($interval->s, 'seconde');
            }
        }

        // We use the two biggest parts
        if(count($format) > 1)
            $format = array_shift($format).' et '.array_shift($format);
        else
            $format = array_pop($format);

        return $interval->format($format);
    }

    public static function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);

        if(3 == strlen($hex)) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function pluralize(int $value, string $text, string $altText = null) {
		if(!empty($altText))
        	return (($value > 1) ? $altText : $text);
		else
        	return $text.(($value > 1) ? $altText : '');
    }
}
