<?php
/**
 * Default user function
 *
 * @package Xaraya
 * @copyright (C) 2005-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
  *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * the main user function
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments. Function decides if user is logged in
 * and returns user to correct location to register.
 * @return bool
 */
function registration_user_main()
{
    $allowregistration = xarModGetVar('registration', 'allowregistration');
    $showterms = xarModGetVar('registration','showterms');
    $showprivacy = xarModGetVar('registration','showprivacy');
    
    if (xarUserIsLoggedIn()) {
        if ($showterms) {
            xarResponseRedirect(xarModURL('registration', 'user', 'terms'));
        } elseif ($showprivacy) {
         xarResponseRedirect(xarModURL('registration', 'user', 'privacy'));
        } else {
            xarResponseRedirect(xarModURL('roles', 'user', 'account'));
        }

    } elseif (FALSE == $allowregistration || $allowregistration ==0) {
        return xarTplModule('registration','user','errors',array('errortype' => 'registration_suspended'));

    } else { //allow user to register

//check is this a hidden url and short urls on??
        $hidemodurl = xarModGetVar('registration','hidemoduleurl');
        $useAliasName= xarModGetVar('registration','useModuleAlias');
        if (($hidemodurl == TRUE) && ($useAliasName == TRUE) ) {
            $currenturl= xarServerGetCurrentURL();
            $testurl = parse_url($currenturl);
               if (isset($testurl['query'])) {
                $isblocked =   stripos($testurl['query'], 'registration');
            } elseif (isset($testurl['path'])) {
                $isblocked =   stripos($testurl['path'], 'registration');
            }
            if ($isblocked == TRUE) {
                 return xarResponseNotFound();
            }
        }

        $minage = xarModGetVar('registration', 'minage');

        if (($minage)>0) {
            xarResponseRedirect(xarModURL('registration','user','register', array('phase'=>'checkage')));
        }else{
            xarResponseRedirect(xarModURL('registration','user','register'));
        }
    }
    return true;
}
?>