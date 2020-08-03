<?php
/**
 * Utility function pass individual menu items to the main menu
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2011skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * utility function to pass individual menu items to the main menu
 *
 * Registration has only one menu item for admins
 * @return array containing the menulinks for the main menu items.
 */
function registration_adminapi_getmenulinks()
{
    $menulinks = array();

    if (xarSecurityCheck('AdminRegistration',0)) {

        $menulinks[] = Array('url'   => xarModURL('registration', 'admin', 'modifyconfig',array('tab'=>'registration')),
                             'title' => xarML('Modify registration options'),
                             'label' => xarML('Registration options'),
                             'active' => array('registration')
                             );
        $menulinks[] = Array('url'   => xarModURL('registration', 'admin', 'modifyconfig',array('tab'=>'general')),
                             'title' => xarML('Modify general configuration options'),
                             'label' => xarML('Modify General Config '),
                             'active' => array('general')
                             );
    }
    return $menulinks;
}

?>