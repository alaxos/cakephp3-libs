<?php
namespace Alaxos\Lib;
/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 */
class StringTool
{
	/**
     * Tests if a string starts with a given string
     *
     * @param     string
     * @param     string
     * @return    bool
     */
    public static function start_with($string, $needle, $case_sensitive = true)
    {
        if(!is_string($string))
        {
            $string = (string)$string;
        }
        
        if(!is_string($needle))
        {
            $needle = (string)$needle;
        }
        
        if($case_sensitive)
        {
            return strpos($string, $needle) === 0;
        }
        else
        {
            return stripos($string, $needle) === 0;
        }
    }
        
    
    /**
     * Tests if a string ends with the given string
     *
     * @param     string
     * @param     string
     * @return    bool
     */
    public static function end_with($string, $needle, $case_sensitive = true)
    {
        if(!is_string($string))
        {
            $string = (string)$string;
        }
        
        if(!is_string($needle))
        {
            $needle = (string)$needle;
        }
        
        if($case_sensitive)
        {
            return strrpos($string, $needle) === strlen($string) - strlen($needle);
        }
        else
        {
            return strripos($string, $needle) === strlen($string) - strlen($needle);
        }
    }
    

    /**
	 * Return the string found between two characters. If an index is given, it returns the
	 * value at the index position
	 *
	 * @param string $opening_char
	 * @param string $closing_char
	 * @param int $index 0 based index
	 * @return string or null
	 */
	public static function get_value_between_chars($haystack, $index = 0, $opening_char = '[', $closing_char = ']')
	{
	    $offset = 0;
	    $found = true;
	    $value = null;
	 
	    for ($i = 0; $i < $index + 1; $i++)
	    {
	        $op_pos = strpos($haystack, $opening_char, $offset);
	        if($op_pos !== false)
	        {
	            $cl_pos = strpos($haystack, $closing_char, $op_pos + strlen($opening_char));
	 
	            if($cl_pos !== false)
	            {
	                $value = substr($haystack, $op_pos + strlen($opening_char), $cl_pos - $op_pos - strlen($opening_char));
	                $offset = $cl_pos + strlen($closing_char);
	            }
	            else
	            {
	                $found = false;
	                break;
	            }
	        }
	        else
	        {
	            $found = false;
	            break;
	        }
	    }
	 
	    if($found)
	    {
	        return $value;
	    }
	    else
	    {
	        return null;
	    }
	}
	
	/**
	 * Return a random string made of lowercase letters, uppercase letters and digits
	 *  
	 * @param number $length The length of the string to get
	 * @return string 
	 */
	public static function get_alphanumeric_random_string($length = 10)
	{
	    return StringTool::get_random_string($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
	}
	
	/**
	 * Return a random string made of the given chars
	 * 
	 * @param number $length The length of the string to get
	 * @param string $chars A string containing the chars that can compose the generated random string
	 * @return string
	 */
	public static function get_random_string($length = 10, $chars)
	{
	    $random_string = '';
	    
	    for($i = 0; $i < $length; $i++)
	    {
	        $index = rand(0, strlen($chars) - 1);
	        $random_string .= $chars{$index};
	    }
	    
	    return $random_string;
	}
	
	/**
     * Ensure a string starts with another given string
     *
     * @param $string The string that must start with a leading string
     * @param $leading_string The string to add at the beginning of the main string if necessary
     * @return string
     */
    public static function ensure_start_with($string, $leading_string)
    {
        if (StringTool :: start_with($string, $leading_string))
        {
            return $string;
        }
        else
        {
            return $leading_string . $string;
        }
    }
    
   /**
    * Ensure a string ends with another given string
    *
    * @param $string The string that must end with a trailing string
    * @param $trailing_string The string to add at the end of the main string if necessary
    * @return string
    */
    public static function ensure_end_with($string, $trailing_string)
    {
        if (StringTool :: end_with($string, $trailing_string))
        {
            return $string;
        }
        else
        {
            return $string . $trailing_string;
        }
    }
    
    
	/**
     * Remove a trailing string from a string if it exists
     * @param $string The string that must be shortened if it ends with a trailing string
     * @param $trailing_string The trailing string
     * @return string
     */
    public static function remove_trailing($string, $trailing_string)
    {
        if (StringTool :: end_with($string, $trailing_string))
        {
            return substr($string, 0, strlen($string) - strlen($trailing_string));
        }
        else
        {
            return $string;
        }
    }
    
    /**
    * Remove a leading string from a string if it exists
    * @param string $string The string that must be shortened if it starts with a leading string
    * @param string $leading_string The leading string
    * @return string
    */
    public static function remove_leading($string, $leading_string)
    {
        if (StringTool :: start_with($string, $leading_string))
        {
            return substr($string, strlen($leading_string));
        }
        else
        {
            return $string;
        }
    }
    
    /**
     * Indicates wether the given string is a valid email address
     * @param $email string
     * @return boolean
     */
    public static function is_valid_email($email)
    {
        if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
        {
            return true;
        }
        
        return false;
    }
    

    public static function shorten($string, $max_length = 100, $ellipse = '...', $clever_cut = true)
    {
        if(function_exists('mb_strlen') && function_exists('mb_substr') && function_exists('mb_strrpos'))
        {
            if(mb_strlen($string) > $max_length)
            {
                $string = mb_substr($string, 0, $max_length - mb_strlen($ellipse));
            
                if($clever_cut)
                {
                    $string = mb_substr($string, 0, mb_strrpos($string, " "));
                }
                
                $string .= $ellipse;
            }
        }
        else
        {
            if(strlen($string) > $max_length)
            {
                $string = substr($string, 0, $max_length - strlen($ellipse));
                
                if($clever_cut)
                {
                    $string = substr($string, 0, strrpos($string, " "));
                }
                
                $string .= $ellipse;
            }
        }
        
        return $string;
    }

    public static function utf8_to_safe_html($utf8_text)
    {
        /*
         * Convert UTF-8 characters to an ASCII encoded character, allowing to display them
         * on any encoded webpage (including accents, chinese characters, arabic characters, etc.)
         */
        mb_substitute_character('entity');
        $safe_html =  mb_convert_encoding($utf8_text, 'US-ASCII', 'UTF-8');
        
        return $safe_html;
    }

    /**
     * Replace the last occurence of a substring in a string
     *
     * @param string $search
     * @param string $replace
     * @param string $string
     */
    public static function last_replace($search, $replace, $string)
    {
        $pos = strrpos($string, $search);
        
        if($pos !== false)
        {
            return substr_replace($string, $replace, $pos, strlen($search));
        }
        else
        {
            return $string;
        }
    }
    
    /**
     * Do a transliteration (é -> e, à -> a, É -> E) on a UTF-8 string
     * 
     * Note:
     *         Using iconv() could maybe do the trick more rapidly, 
     *         but as it highly depends on the system libraries, it makes the result unpredictable
     *         
     *         iconv('UTF-8', 'us-ascii//TRANSLIT//IGNORE', $initial);
     *         
     * @param string $utf8_text UTF8 string
     * @return string
     */
    public static function remove_accents($utf8_text)
    {
        return strtr(utf8_decode($utf8_text),utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇČÈÉÊËÌÍÎÏÑÒÓÔÕÖØŚŞŠÙÚÛÜÝŽ'), 
                                             utf8_decode('aaaaaceeeeiiiinooooouuuuyyAAAAACCEEEEIIIINOOOOOOSSSUUUUYZ'));
    }
    
    public static function mb_ucfirst($text)
    {
        if(function_exists('mb_substr') && function_exists('mb_strtoupper'))
        {
            $letter1 = mb_substr($text, 0, 1);
            $rest    = mb_substr($text, 1);
            
            $letter1_uc = mb_strtoupper($letter1);
            $utext      = $letter1_uc . $rest;
            
            return $utext;
        }
        else
        {
            $word = ucfirst($text);
            
            /*
             * Case of special chars
            */
            $special_chars = array(
                'à' => 'À',
                'á' => 'Á',
                'â' => 'Â',
                'ä' => 'Ä',
                'ã' => 'Ã',
                
                'é' => 'É',
                'è' => 'È',
                'ê' => 'Ê',
                'ë' => 'Ë',
                'ẽ' => 'Ẽ',
                
                'ì' => 'Ì',
                'í' => 'Í',
                'î' => 'Î',
                'ï' => 'Ï',
                'ĩ' => 'Ĩ',
                
                'ò' => 'Ò',
                'ó' => 'Ó',
                'ô' => 'Ô',
                'ö' => 'Ö',
                'õ' => 'Õ',
                
                'ù' => 'Ù',
                'ú' => 'Ú',
                'û' => 'Û',
                'ü' => 'Ü',
                'ũ' => 'Ũ',
            );
            
            foreach($special_chars as $min => $maj)
            {
                if(StringTool :: start_with($text, $min))
                {
                    $sub_upper_cased = substr($word, 2);
                    $word = $maj . $sub_upper_cased;
                    
                    break;
                }
            }
            
            return $word;
        }
    }
}
?>