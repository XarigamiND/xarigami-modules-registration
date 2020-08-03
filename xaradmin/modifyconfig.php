<?php
/**
 * Modify configuration
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
 * modify configuration
 */
function registration_admin_modifyconfig()
{
    // Security Check
    if (!xarSecurityCheck('AdminRegistration')) return;
    if (!xarVarFetch('phase',    'str:1:100', $phase,      'modify', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('shorturls','checkbox',  $shorturls,   FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('tab',      'str:1:100', $data['tab'], 'general', XARVAR_NOT_REQUIRED)) return;

    //common vars
    $data['allowregistration']    = xarModGetVar('registration','allowregistration');

    switch (strtolower($phase)) {
        case 'modify':
        default:
            $data['authid'] = xarSecGenAuthKey();
            switch ($data['tab']) {
                case 'general':
                    $data['shorturlschecked'] = xarModGetVar('registration', 'SupportShortURLs') ? true : false;
                    $data['showterms']        = xarModGetVar('registration', 'showterms') ? true : false;
                    $data['showprivacy']      = xarModGetVar('registration', 'showprivacy') ? true : false;
                    $data['customprivacy']      = xarModGetVar('registration', 'customprivacy');
                    $data['customterms']      = xarModGetVar('registration', 'customterms');
                    $data['usealiasname']     = xarModGetVar('registration', 'useModuleAlias');
                    $data['aliasname']        = xarModGetVar('registration','aliasname');
                    $data['hidemoduleurl']   = xarModGetVar('registration','hidemoduleurl');
                    break;
                case 'registration':
                    // create the dropdown of groups for the template display
                    // get the array of all groups
                    // remove duplicate entries from the list of groups
                    $roles  = new xarRoles();
                    $groups = array();
                    $names  = array();
                    $groupdropdown = array();
                    foreach($roles->getgroups() as $temp) {
                        $nam = $temp['name'];
                        if (!in_array($nam, $names)) {
                            array_push($names, $nam);
                            array_push($groups, $temp);
                            $groupdropdown[] =array('id'=>$nam, 'name'=>$nam);
                        }
                    }
                    $data['groupdropdown']= $groupdropdown;//leave groups incase someone is using that array in templates
                    $data['groups'] = $groups;
                    //Use the same modvar here. It is now putback in Roles again so Roles can use the var too without mod dependencies.
                    $data['defaultgroup']         = xarModGetVar('roles', 'defaultgroup');
                    $notifyemail                  = xarModGetVar('registration','notifyemail');

                    $data['minage']               = xarModGetVar('registration','minage');
                    $data['chooseownpassword']    = xarModGetVar('registration','chooseownpassword');
                    $data['repeatemail']          = xarModGetVar('registration','repeatemail');
                    $data['explicitapproval']     = xarModGetVar('registration', 'explicitapproval');
                    $data['sendnotice']           = xarModGetVar('registration','sendnotice');
                    $data['requirevalidation']    = xarModGetVar('registration','requirevalidation');
                    //from roles module
                    $data['uniquedisplay']    = xarModGetVar('roles','uniquedisplay');
                    $data['requiredisplayname']    = xarModGetVar('roles','requiredisplayname');

                    $data['showdynamic']          = xarModGetVar('registration','showdynamic');
                    $data['unqiquemail']          = xarModGetVar('registration','uniqueemail');
                    $data['sendwelcomeemail']     = xarModGetVar('registration','sendwelcomeemail');
                    $data['allowinvisible']       = xarModGetVar('registration','allowinvisible');
                    $data['minpasslength']       = xarModGetVar('roles','minpasslength');
                    if (!isset($notifyemail) || trim ($notifyemail)=='')$notifyemail = xarModGetVar('mail','adminmail');

                    $data['notifyemail'] =  $notifyemail;
                    break;


                default:
                    break;
            }

            break;

        case 'update':
            // Confirm authorisation code
            if (!xarSecConfirmAuthKey()) return;
            switch ($data['tab']) {
                case 'general':
                default:
                    if (!xarVarFetch('showterms',   'checkbox', $showterms,   FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('showprivacy', 'checkbox', $showprivacy, FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('customprivacy', 'isset', $customprivacy, '',  XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('customterms', 'isset', $customterms, '',  XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('aliasname',    'str:1:',   $aliasname, '', XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('modulealias',  'checkbox', $modulealias,FALSE,XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('hidemoduleurl',  'checkbox', $hidemoduleurl,FALSE,XARVAR_NOT_REQUIRED)) return;
                    xarModSetVar('registration', 'SupportShortURLs', $shorturls);
                    xarModSetVar('registration', 'showterms', $showterms);
                    xarModSetVar('registration', 'showprivacy', $showprivacy);
                    xarModSetVar('registration', 'showprivacy', $showprivacy);
                    xarModSetVar('registration', 'customprivacy', $customprivacy);
                    xarModSetVar('registration', 'customterms', $customterms);
                    if (isset($aliasname) && trim($aliasname)<>'') {
                        xarModSetVar('registration', 'useModuleAlias', $modulealias);
                    } else{
                         xarModSetVar('registration', 'useModuleAlias', 0);
                    }
                    $currentalias = xarModGetVar('registration','aliasname');
                    $newalias = trim($aliasname);
                    /* Get rid of the spaces if any, it's easier here and use that as the alias*/
                    if ( strpos($newalias,'_') === FALSE )
                    {
                        $newalias = str_replace(' ','_',$newalias);
                    }
                    $hasalias= xarModGetAlias($currentalias);
                    $useAliasName= xarModGetVar('registration','useModuleAlias');
                            // if a new one is set or if there is an old one there and we don't want to use alias anymore
                    if ($useAliasName && !empty($newalias)) {
                         if ($aliasname != $currentalias)
                         /* First check for old alias and delete it */
                            if (isset($hasalias) && ($hasalias =='registration')){
                                xarModDelAlias($currentalias,'registration');
                            }
                            /* now set the new alias if it's a new one */
                            $newalias = xarModSetAlias($newalias,'registration');
                            if (!$newalias) { //name already taken so unset
                                 xarModSetVar('registration', 'aliasname', '');
                                 xarModSetVar('registration', 'useModuleAlias', FALSE);
                                  xarModSetVar('registration', 'hidemoduleurl', FALSE);
                            } else { //it's ok to set the new alias name
                                xarModSetVar('registration', 'aliasname', $aliasname);
                                xarModSetVar('registration', 'useModuleAlias', $modulealias);
                                 xarModSetVar('registration', 'hidemoduleurl', $hidemoduleurl);
                            }
                    } else {
                       //remove any existing alias and set the vars to none and false
                            if (isset($hasalias) && ($hasalias =='registration')){
                                xarModDelAlias($currentalias,'registration');
                            }
                            xarModSetVar('registration', 'aliasname', '');
                            xarModSetVar('registration', 'useModuleAlias', FALSE);
                               xarModSetVar('registration', 'hidemoduleurl', FALSE);
                    }
                    $msg = xarML('General registration options have been successfully saved.');
                    xarTplSetMessage($msg,'status');

                    break;
                case 'registration':
                    if (!xarVarFetch('defaultgroup',      'str:1',    $defaultgroup,     'Users', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
                    if (!xarVarFetch('allowregistration', 'checkbox', $allowregistration, FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('chooseownpassword', 'checkbox', $chooseownpassword, FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('repeatemail',       'checkbox', $repeatemail, FALSE, XARVAR_NOT_REQUIRED)) return;

                    if (!xarVarFetch('minage',            'str:1:3:', $minage,            '13', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
                    if (!xarVarFetch('sendnotice',        'checkbox', $sendnotice,        FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('explicitapproval',  'checkbox', $explicitapproval,  FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('requirevalidation', 'checkbox', $requirevalidation, FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('showdynamic',       'checkbox', $showdynamic,       FALSE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('sendwelcomeemail',  'checkbox', $sendwelcomeemail,  FALSE, XARVAR_NOT_REQUIRED)) return;

                    if (!xarVarFetch('notifyemail',       'str:1:150',$notifyemail,       xarModGetVar('mail', 'adminmail'), XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('uniquedisplay',      'checkbox', $uniquedisplay, TRUE, XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('requiredisplayname',      'checkbox', $requiredisplayname, TRUE, XARVAR_NOT_REQUIRED)) return;

                    xarModSetVar('registration', 'chooseownpassword', $chooseownpassword);
                    xarModSetVar('registration', 'repeatemail', $repeatemail);
                    xarModSetVar('roles', 'defaultgroup', $defaultgroup);
                    xarModSetVar('registration', 'allowregistration', $allowregistration);
                    xarModSetVar('registration', 'minage', $minage);
                    xarModSetVar('registration', 'notifyemail', $notifyemail);
                    xarModSetVar('registration', 'sendnotice', $sendnotice);
                    xarModSetVar('registration', 'explicitapproval', $explicitapproval);
                    xarModSetVar('registration', 'requirevalidation', $requirevalidation);
                    xarModSetVar('registration', 'showdynamic', $showdynamic);
                    xarModSetVar('registration', 'sendwelcomeemail', $sendwelcomeemail);
                    //roles module vars - here for convenience
                    xarModSetVar('roles', 'uniquedisplay',$uniquedisplay);
                    xarModSetVar('roles', 'requiredisplayname',$requiredisplayname);
                    $msg = xarML('Registration configuration options have been successfully saved.');
                    xarTplSetMessage($msg,'status');

                    break;

            }

            xarResponseRedirect(xarModURL('registration', 'admin', 'modifyconfig', array('tab' => $data['tab'])));
            // Return
            return true;
            break;
    }

    //common adminmenu
    $data['menulinks'] = xarModAPIFunc('registration','admin','getmenulinks');
    return $data;
}
?>