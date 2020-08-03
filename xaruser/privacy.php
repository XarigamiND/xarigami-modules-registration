<?php
/**
 * Shows the privacy policy if set as a modvar
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/registration
 */
/**
 * Shows the privacy policy if set as a modvar
 * @author  Registration Module Development Team
 * @return array Empty array, the privacy info is in the template itself
 */
function registration_user_privacy()
{
    // Security check
    if (!xarSecurityCheck('ViewRegistration')) return;
    $data = array();
    //set some variables used in templates
    //remain compatible with prior versions
    $data['privacylink']    = xarModURL('registration','user','privacy');
    $data['reglink']        = xarModURL('registration','user','main');
    $data['termslink']      = xarModURL('registration','user','terms');
    $data['minage']         = $data['mina'] = xarModGetVar('registration','minage');
    $contmail       = xarModGetVar('mail', 'adminmail',1);
    $data['contmail'] = xarModAPIFunc('mail','user','obfuemail',array('email'=>$contmail, 'text'=>xarML('our webmaster'),'obmethod'=>0));
    $data['showterms']      = xarModGetVar('registration', 'showterms');
    $data['customterms']    = xarModGetVar('registration', 'customterms');
    $data['showprivacy']    = xarModGetVar('registration', 'showprivacy');
    $data['customprivacy']    = xarModGetVar('registration', 'customprivacy');
    $data['minagewords'] = array(
                        '0'=>xarML('zero'),
                        '1'=>xarML('one'),
                        '2'=>xarML('two'),
                        '3'=>xarML('three'),
                        '4'=>xarML('four'),
                        '5'=>xarML('five'),
                        '6'=>xarML('six'),
                        '7'=>xarML('seven'),
                        '8'=>xarML('eight'),
                        '9'=>xarML('nine'),
                        '10'=>xarML('ten'),
                        '11'=>xarML('eleven'),
                        '12'=>xarML('twelve'),
                        '13'=>xarML('thirteen'),
                        '14'=>xarML('fourteen'),
                        '15'=>xarML('fiveteen'),
                        '16'=>xarML('sixteen'),
                        '17'=>xarML('seventeen'),
                        '18'=>xarML('eighteen'),
                        '19'=>xarML('nineteen'),
                        '20'=>xarML('twenty'),
                        '21'=>xarML('twenty-one'),
                        );
    //common adminmenu
    $data['menulinks'] = xarModAPIFunc('registration','user','getmenulinks');

    xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Privacy Policy')));
    return $data;

}
?>