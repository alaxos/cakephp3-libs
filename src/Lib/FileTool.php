<?php
namespace Alaxos\Lib;

use Cake\Core\Configure;

/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 */
class FileTool
{
	/**
     * Depending on the server installation, the mime magic file may be located at a different place
     * (default is /etc/magic).
     *
     * A specific path may be specified in:
     * 	- php.ini : with the 'mime_magic.magicfile' config
     *  - At the CakePHP application level by using Configure::write('alaxos.mime_magic.magicfile', '/path/to/file');
     */
    public static function getMagicFilepath()
    {
        $app_magic_filepath = Configure::read('alaxos.mime_magic.magicfile');
        
        if(isset($app_magic_filepath) && !empty($app_magic_filepath))
        {
            return $app_magic_filepath;
        }
        else
        {
            $cfg_var_magic_filepath = get_cfg_var('mime_magic.magicfile');
            
            if(isset($cfg_var_magic_filepath) && !empty($cfg_var_magic_filepath))
            {
                return $cfg_var_magic_filepath;
            }
        }
        
        return null;
    }

    public static function getMimetype($file_path, $options = array())
    {
        $default_options = array('short_description' => true);
        
        $options = array_merge($default_options, $options);
        
        if(function_exists('finfo_open') && is_readable($file_path))
        {
            $magic_filepath = static::getMagicFilepath();
            
            if(isset($magic_filepath))
            {
                $finfo = finfo_open(FILEINFO_MIME, $magic_filepath);
            }
            else
            {
                $finfo = finfo_open(FILEINFO_MIME);
            }
            
            $mimetype = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            
            if($options['short_description'] && strpos($mimetype, ';') !== false)
            {
                $mimetype = substr($mimetype, 0, strpos($mimetype, ';'));
            }
            
            return $mimetype;
        }
        
        return false;
    }
    
    public static function getPrintableFileSize($file_path)
    {
        if(is_readable($file_path))
        {
            return static::getPrintableSize(filesize($file_path));
        }
        
        return false;
    }
    
    public static function getPrintableSize($bytes)
    {
        if(is_numeric($bytes))
        {
            $size = $bytes/1024;
            if($size < 1000)
            {
                $printable_size = round($size) . ' Kb';
            }
            else
            {
                $printable_size =  round($size/1024, 1) . ' MB';
            }
            
            return $printable_size;
        }
        
        return false;
    }
}