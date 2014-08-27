<?php 
namespace Alaxos\Lib;

use Cake\Core\Configure;
class I18nTool
{
    public static function set_current_locale($locale)
    {
        if(isset($locale))
        {
            if(is_string($locale))
            {
                $locale   = strtolower($locale);
                $language = null;
                
               /*
                * Depending on the server configuration, the locale that the method 'setlocale()'
                * is waiting for may be different.
                *
                * But as the 'setlocale()' can take an array of strings, we try to pass
                * an array instead of a string
                */
                switch($locale)
                {
                	case 'fr':
                	case 'fre':
                	case 'fra':
                	case 'fren':
                	case 'french':
                	case 'fr_ch':
                	case 'fr_fr':
                	case 'fr_ch.utf-8':
                	case 'fr_fr.utf-8':
                	case 'fr-ch':
                	case 'fr-fr':
                	case 'fr-ch.utf-8':
                	case 'fr-fr.utf-8':
                	    $locale   = array('fr_CH.UTF-8', 'fr_CH', 'fr_FR.UTF-8', 'fr_FR');
                	    $language = 'fra';
                	    break;
        
                	case 'en':
                	case 'eng':
                	case 'english':
                	case 'en_en':
                	case 'en_us':
                	case 'en_us.utf-8':
                	case 'en_en.utf-8':
                	case 'en-en':
                	case 'en-us':
                	case 'en-us.utf-8':
                	case 'en-en.utf-8':
                	    $locale = array('en_US.UTF-8', 'en_US', 'en_EN.UTF-8', 'en_EN');
                	    $language = 'en';
                	    break;
                	    	
                	case 'ger':
                	case 'de':
                	case 'german':
                	case 'de_de':
                	case 'de-de':
                	case 'de_de.utf-8':
                	case 'de-de.utf-8':
                	    $locale = array('de_CH.UTF-8', 'de_CH', 'de_DE.UTF-8', 'de_DE', 'de_DE@euro');
                	    $language = 'ger';
                	    break;
                	    	
                	case 'es':
                	case 'spa':
                	case 'spanish':
                	case 'es_es':
                	case 'es_es.utf-8':
                	    $locale = array('es_ES.UTF-8', 'es_ES', 'es_ES@euro');
                	    $language = 'spa';
                	    break;
                	    	
                	default:
                	    $locale = array($locale);
                	    break;
                }
            }
            
            $new_locale = setlocale(LC_ALL, $locale);
            
            if(isset($language))
            {
                Configure::write('Config.language', $language);
            }
            
            
            
            if(stripos(strtolower($new_locale), 'utf-8') !== false)
            {
                header('Content-Type: text/html; charset=UTF-8');
            }
            
            
            return $new_locale;
        }
    }
    
}