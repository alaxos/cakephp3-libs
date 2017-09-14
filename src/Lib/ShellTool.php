<?php
namespace Alaxos\Lib;

class ShellTool
{
    public static $colors = [
        'red' => ['start' => "\033[01;31m", 'close' => "\033[0m"]
    ];

    /**
     * Return the given text formatted in the given color
     *
     * @param string $text
     * @param string $color
     * @return string
     */
    public static function color($text, $color)
    {
        if (isset(ShellTool::$colors[$color])) {
            return ShellTool::$colors[$color]['start'] . $text . ShellTool::$colors[$color]['close'];
        } else {
            return $text;
        }
    }

    /**
     * Return the given text formatted in red
     *
     * @param string $text
     * @return string
     */
    public static function red($text)
    {
        return ShellTool::color($text, 'red');
    }
}