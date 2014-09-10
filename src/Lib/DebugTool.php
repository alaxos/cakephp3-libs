<?php
namespace Alaxos\Lib;

use DOMDocument;

/**
 * Utility class to do some debug and benchmarks
 *
 * @author Nicolas Rod (nico@alaxos.com)
 * @version 2011-11-25
 */
class DebugTool
{
    public static $enabled = true;
    
    private static $start_microtime;
    private static $last_microtime;
    private static $alert_threshold;
    private static $force_show_file;
    
    public static function show($object, $title = null, $backtrace_index = 0)
    {
        if(DebugTool::$enabled)
        {
            if(PHP_SAPI != 'cli')
            {
                echo '<pre>';
            }
            
            $calledFrom = debug_backtrace();
            echo "\n" . $calledFrom[$backtrace_index]['file'];
            echo ' (line ' . $calledFrom[$backtrace_index]['line'] . ")\n";
            
            if (isset($title))
            {
                echo "\n" . $title . "\n";
            }
            
            if (is_array($object))
            {
                print_r($object);
            }
            elseif (is_a($object, 'DOMDocument'))
            {
                echo 'DOMDocument:' . "\n\n";
                
                $object->formatOutput = true;
                $object->preserveWhiteSpace = false;
                $xml_string = $object->saveXML();
                
                if(PHP_SAPI != 'cli')
                {
                    echo htmlentities($xml_string) . "\n";
                }
                else
                {
                    echo $xml_string . "\n";
                }
            }
            elseif (is_a($object, 'DOMNodeList') || is_a($object, 'DOMElement'))
            {
                $dom = new DOMDocument();
                $debugElement = $dom->createElement('debug');
                $dom->appendChild($debugElement);
                
                if (is_a($object, 'DOMNodeList'))
                {
                    echo 'DOMNodeList:'."\n\n";
                    
                    foreach ($object as $node)
                    {
                        $node = $dom->importNode($node, true);
                        $debugElement->appendChild($node);
                    }
                }
                elseif (is_a($object, 'DOMElement'))
                {
                    echo 'DOMElement:'."\n\n";
                    
                    $node = $dom->importNode($object, true);
                    $debugElement->appendChild($node);
                }
                
                $dom->formatOutput = true;
                $dom->preserveWhiteSpace = false;
                $xml_string = $dom->saveXML();
                
                if(PHP_SAPI != 'cli')
                {
                    echo htmlentities($xml_string) . "\n";
                }
                else
                {
                    echo $xml_string . "\n";
                }
            }
            elseif (is_object($object))
            {
                echo print_r($object);
            }
            else
            {
                echo $object . "\n";
            }
            
            if(PHP_SAPI != 'cli')
            {
                echo '</pre>';
            }
        }
    }

    /**
     * Init the start time to calculate steps time
     *
     * @param float $alert_threshold Threshold in second above which the step time must be shown in red
     */
    public static function init_microtime($alert_threshold = null, $force_show_file = false)
    {
        if(DebugTool::$enabled)
        {
            DebugTool :: $start_microtime  = microtime(true);
            DebugTool :: $last_microtime   = DebugTool :: $start_microtime;
            DebugTool :: $alert_threshold  = $alert_threshold;
            DebugTool :: $force_show_file  = $force_show_file;
        }
    }
    
    public static function microtime($title = null, $data = null)
    {
        if(DebugTool::$enabled)
        {
            if(!isset(DebugTool :: $start_microtime))
            {
                DebugTool :: init_microtime();
            }
            
            $current_microtime           = microtime(true);
            
            echo '<pre class="debug">';
            
            echo (isset($title) ? $title . ': ' : null);
            echo number_format(($current_microtime - DebugTool :: $start_microtime), 3);
            echo ' (';
            
            $last_operation_time = $current_microtime - DebugTool :: $last_microtime;
            if(isset(DebugTool::$alert_threshold) && $last_operation_time > DebugTool::$alert_threshold)
            {
                $calledFrom = debug_backtrace();
                echo '<span style="color:red">' . $calledFrom[0]['file'] . ' (line ' . $calledFrom[0]['line'] . ') ' . number_format($last_operation_time, 5) . '</span>';
            }
            elseif(DebugTool::$force_show_file)
            {
                $calledFrom = debug_backtrace();
                echo $calledFrom[0]['file'] . ' (line ' . $calledFrom[0]['line'] . ') ' . number_format($last_operation_time, 5);
            }
            else
            {
                echo number_format($last_operation_time, 5);
            }
            
            echo ')';
            
            if(isset($data))
            {
                echo '<div style="padding-left:30px">';
                DebugTool::show($data, null, 1);
                echo '</div>';
            }
            
            echo '</pre>';
            
            DebugTool :: $last_microtime = $current_microtime;
        }
    }
}

?>