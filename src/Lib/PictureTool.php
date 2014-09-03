<?php
namespace Alaxos\Lib;

/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 */
class PictureTool
{
    public static function createThumbnail($picture_path, $thumbnail_path, $options = array())
    {
        $default_options = ['max_width'       => null,
                            'max_height'      => null,
                            'mimetype'        => null,
                            'copy_if_smaller' => true,
                            'force_creation'  => false];
        
        $options = array_merge($default_options, $options);
        
        if(is_readable($picture_path) && (isset($options['max_width']) || isset($options['max_height']) || (isset($options['force_creation']) && $options['force_creation'])))
        {
            if(!isset($options['mimetype']))
            {
                $mimetype = FileTool::getMimetype($picture_path);
            }
            else
            {
                $mimetype = $options['mimetype'];
            }
            
            switch($mimetype)
            {
                case 'image/jpeg':
                   
                   /*
                    * On some PHP + GD config, some warnings about image may prevent to create an image from file
                    * -> disable warnings
                    */
                    ini_set('gd.jpeg_ignore_warning', 1);
                    
                    $img = imagecreatefromjpeg($picture_path);
                    break;
                    
                case 'image/png':
                    
                    $img = imagecreatefrompng($picture_path);
                    break;
                    
                case 'image/gif':
                    
                    $img = imagecreatefromgif($picture_path);
                    break;
                    
                case 'image/x-ms-bmp': //Windows bitmaps can not be resized with GD library
                default:
                    return false;
            }
            
            if(isset($img) && $img !== false)
            {
                $x   = imagesx($img);
                $y   = imagesy($img);
                
                $max_width  = isset($options['max_width'])  ? $options['max_width']  : $x;
                $max_height = isset($options['max_height']) ? $options['max_height'] : $y;
                
                /* small thumbnails *****************/
                
                if($x > $max_width || $y > $max_height)
                {
                    if($x > $y)
                    {
                        $nx = $max_width;
                        $ny = $nx / $x * $y;
                        
                        if($ny > $max_height)
                        {
                            $ny = $max_height;
                            $nx = $ny / $y * $x;
                        }
                    }
                    else
                    {
                        $ny = $max_height;
                        $nx = $ny / $y * $x;
                        
                        if($nx > $max_width)
                        {
                            $nx = $max_width;
                            $ny = $nx / $x * $y;
                        }
                    }
                }
                elseif(isset($options['copy_if_smaller']) && $options['copy_if_smaller'])
                {
                    copy($picture_path, $thumbnail_path);
                    chmod($thumbnail_path, 0777);
                    
                    return true;
                }
                elseif(isset($options['force_creation']) && $options['force_creation'])
                {
                    $nx = $x;
                    $ny = $y;
                }
                
                if(isset($nx) && isset($ny))
                {
                    $nimg = imagecreatetruecolor($nx, $ny);
                    
                    /*
                     * preserve transparency
                     */
                    if($mimetype == "image/gif" or $mimetype == "image/png")
                    {
                        imagecolortransparent($nimg, imagecolorallocatealpha($nimg, 0, 0, 0, 127));
                        imagealphablending($nimg, false);
                        imagesavealpha($nimg, true);
                    }
                    
                    imagecopyresampled($nimg, $img, 0, 0, 0, 0, $nx, $ny, $x, $y);
                    
                    switch($mimetype)
                    {
                        case 'image/jpeg':
                            imagejpeg($nimg, $thumbnail_path);
                            break;
                            
                        case 'image/png':
                            imagepng($nimg, $thumbnail_path);
                            break;
                        
                        case 'image/gif':
                            imagegif($nimg, $thumbnail_path);
                            break;
                        
                        case 'image/x-ms-bmp': //Windows bitmaps can not be resized with GD library
                        default:
                            break;
                    }
                    
                    @chmod($thumbnail_path, 0777);
                    
                    return true;
                }
            }
        }
        
        return false;
    }

    public static function getDimension($picture_path)
    {
        if(is_readable($picture_path))
        {
            list($width, $height) = getimagesize($picture_path);
            
            return compact('width', 'height');
        }
        else
        {
            return false;
        }
    }
    
    public static function getWidth($picture_path)
    {
        if(is_readable($picture_path))
        {
            $dimension = static::getDimension($picture_path);
            
            return $dimension['width'];
        }
        else
        {
            return false;
        }
    }
    
    public static function getHeight($picture_path)
    {
        if(is_readable($picture_path))
        {
            $dimension = static::getDimension($picture_path);
            
            return $dimension['height'];
        }
        else
        {
            return false;
        }
    }
}