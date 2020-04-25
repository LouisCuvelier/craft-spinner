<?php


namespace louiscuvelier\spinner\helpers;


/**
 * Spintax - A helper class to process Spintax strings.
 *
 * @author Jason Davis - https://www.codedevelopr.com/
 *
 * Tutorial: https://www.codedevelopr.com/articles/php-spintax-class/
 * Gist code : https://gist.github.com/irazasyed/11256369
 *
 * Updated with suggested performance improvement by @PhiSYS.
 */
class Spintax
{
    public function process($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*?)\}/x',
            array($this, 'replace'),
            $text
        );
    }

    public function replace($text)
    {
        $text = $this->process($text[1]);
        $parts = explode('|', $text);
        return $parts[array_rand($parts)];
    }
}