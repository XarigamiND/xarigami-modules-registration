<?php
/**
 * Overview displays standard Overview page
 *
 * @package Xaraya
 * @copyright (C) 2005-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/registration
 */
/**
 * Overview displays standard Overview page
 *
 * Used to call the template that provides display of the overview
 *
 * @return array xarTplModule with $data containing template data
 *         array containing the menulinks for the overview item on the main manu
 * @since 2 Nov 2005
 */
function registration_admin_overview()
{
   /* Security Check */
    if (!xarSecurityCheck('EditRegistration')) return;

    $data=array();

    /* if there is a separate overview function return data to it
     * else just call the main function that usually displays the overview
     */
    //common adminmenu
    $data['menulinks'] = xarModAPIFunc('registration','admin','getmenulinks');
    return xarTplModule('registration', 'admin', 'main', $data,'main');
}

?>