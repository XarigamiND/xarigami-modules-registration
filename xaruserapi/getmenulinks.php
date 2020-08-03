<?php
/**
 * Standard function to get main menu links
 *
 * @package Xaraya
 * @copyright (C) 2005-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Standard function to get main menu links
 * @return array
 */
function registration_userapi_getmenulinks()
{

    if (xarModGetVar('registration', 'allowregistration')){
    // Security check

        if (!xarUserIsLoggedIn()){
            $menulinks[] = array('url'   => xarModURL('registration', 'user', 'main') , //main will check for age issues
                                 'title' => xarML('Site membership, registration and information'),
                                 'label' => xarML('Site Membership'),
                                 'active' => array('register','checkage'),
                                 'activelabels' => array('',xarML('Age check'))
                                 );
        }
    }
    if (xarModGetVar('registration', 'showprivacy')){
        $menulinks[] = array('url'   => xarModURL('registration', 'user', 'privacy'),
                             'title' => xarML('Privacy Policy for this Website'),
                             'label' => xarML('Privacy Policy'),
                             'active' => array('privacy')

                             );
    }
    if (xarModGetVar('registration', 'showterms')){
        $menulinks[] = array('url'   => xarModURL('registration', 'user', 'terms'),
                             'title' => xarML('Terms of Use for this website'),
                             'label' => xarML('Terms of Use'),
                             'active' => array('terms')
                             );
    }



    return $menulinks;
}

?>