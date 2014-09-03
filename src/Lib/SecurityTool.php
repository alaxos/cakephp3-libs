<?php
namespace Alaxos\Lib;

use Cake\Utility\Security;

/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 */
class SecurityTool
{
    private static $token_salt = 'Get me if you can !';
    
    public static function get_valid_fieldnames()
    {
        $today_fieldname     = $this->get_today_fieldname();
        $yesterday_fieldname = $this->get_yesterday_fieldname();
        
        return array($today_fieldname, $yesterday_fieldname);
    }
    
    public static function get_today_fieldname($salt = null)
    {
        return Security::hash($salt . date('d.M.Y', time()), null, true);
    }
    
    public static function get_yesterday_fieldname($salt = null)
    {
        return Security::hash($salt . date('d.M.Y', strtotime(date('Y-m-d')) - 1), null, true);
    }
    
    public static function get_today_token($salt = null)
    {
        return Security::hash($salt . SecurityTool::$token_salt . date('d.M.Y', time()), null, true);
    }
    
    public static function get_yesterday_token($salt = null)
    {
        return Security::hash($salt . SecurityTool::$token_salt . date('d.M.Y', strtotime(date('Y-m-d')) - 1), null, true);
    }
}