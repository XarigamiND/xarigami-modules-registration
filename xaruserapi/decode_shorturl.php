<?php
/**
 * Extract function and arguments from short URLs for this module
 *
 * @package Xarigami
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * extract function and arguments from short URLs for this module, and pass
 * them back to xarGetRequestInfo()
 *
 * Supported URLs :
 *
 * /registration/privacy
 * /registration/terms
 *
 * /registration/register
 * /registration/checkage
 * /registration
 * @param $params array containing the different elements of the virtual path
 * @return array containing func the function to be called and args the query
 *         string arguments, or empty if it failed
 */
function registration_userapi_decode_shorturl($params)
{
    $args = array();
    $module = 'registration';
    $usealias = false;
    $aliasname = NULL;
    $hidemodurl = xarModGetVar('registration','hidemoduleurl');
    $obfuscated = FALSE;
    if (($hidemodurl == TRUE) && ($params[0] == $module)) {
        $obfuscated = TRUE;
        //this will go to a valid url if it returns now
        //we want to hide that. Let's send it directly to the not found page
        //jojo - fix this - right response but code is not finished/correct :)
        return xarResponseNotFound();
    } else {
        /* Check and see if we have a module alias */
        if ($params[0] != $module) { /* it's possibly some type of alias */
            $aliasisset = xarModGetVar('registration', 'useModuleAlias');
            $aliasname  = xarModGetVar('registration','aliasname');
            if (($aliasisset) && isset($aliasname)) {
                $usealias   =TRUE;
            }
        }
        if (empty($params[1])) {
            // nothing specified -> we'll go to the main function
            return array('main', $args);

        } elseif (preg_match('/^index/i',$params[1])) {
            // some search engine/someone tried using index.html (or similar)
            // -> we'll go to the main function
            return array('main', $args);

        } elseif (preg_match('/^terms/i',$params[1])) {
            return array('terms', $args);

        } elseif (preg_match('/^privacy/i',$params[1])) {
            return array('privacy', $args);

        } elseif (preg_match('/^register/i',$params[1])) {
            if (!empty($params[2])) {
                if ($params[2] == 'registration' || $params[2] == 'registerform') {
                    $args['phase'] = 'registerform';
                } elseif ($params[2] == 'checkage') {
                    $args['phase'] = 'checkage';
                } else {
                    // unsupported phase - must be passed via forms
                }
            } else {

            }
            return array('register', $args);

        } else {
         //not supported
        }
    }
    // default : return nothing -> no short URL decoded
}

?>