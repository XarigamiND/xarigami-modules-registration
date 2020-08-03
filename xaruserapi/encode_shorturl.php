<?php
/**
 * Return the path for a short URL to xarModURL for this module
 *
 * @package Xarigami
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2006-2011 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * return the path for a short URL to xarModURL for this module
 *
 * Supported URLs :
 *
 * /registration/privacy
 * /registration/terms
 *
 * /registration/register
 * /registration/checkage
 * /registration
 * @author the roles module development team
 * @param $args the function and arguments passed to xarModURL
 * @return string path to be added to index.php for a short URL, or empty if failed
 */
function registration_userapi_encode_shorturl($args)
{
    // Get arguments from argument array
    extract($args);
    if (!isset($func)) {return;}

    $path = array();
    $get  = $args;
    $module = 'registration';

    $aliasisset = xarModGetVar($module, 'useModuleAlias');
    $aliasname  = xarModGetVar($module, 'aliasname');


    if (!empty($aliasisset) && !empty($aliasname)) {
        $module_for_alias = xarModGetAlias($aliasname);

        if ($module_for_alias == $module) {
            $module = $aliasname;
        }
    }

    $path[] = $module;

    if ($func == 'main') {
         $path[] = '';
        // Consume the 'func' parameter only.
        unset($get['func']);
    } elseif ($func == 'terms' || $func == 'privacy') {
        $path[] = $func;
        unset($get['func']);
    } elseif ($func == 'register') {
            $path[] = 'register';
            if (!empty($phase)) {
                // Bug 4404: registerform and registration are aliases.
                 if ($phase =='checkage' || $phase =='registerform' || $phase=='registration') {
                        $path[] = $phase=='checkage'?'checkage':$phase;
                 } else {
                    // unsupported phase - must be passed via forms
                }
            }
            unset($get['func']);
            unset($get['phase']);
            unset($args['phase']);
            unset($phase);
    } else {
        //hmmm
    }
    return array('path' => $path, 'get' => $get);
}
?>