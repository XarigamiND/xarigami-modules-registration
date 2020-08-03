<?php
/**
 * Initialise the registration module
 *
 * @package Xaraya
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/registration
 */

/**
 * Initialise the registration module
 *
 * @author jojodee <jojodee@xaraya.com>
 * @access public
 * @param none $
 * @returns bool
 */
function registration_init()
{
/** --------------------------------------------------------
 * Set up masks
 */
    xarRegisterMask('ViewRegistration','All','registration','All','All','ACCESS_OVERVIEW');
    xarRegisterMask('ViewRegistrationLogin','All','blocks','Block','registration:rlogin:All','ACCESS_OVERVIEW');
    xarRegisterMask('EditRegistration','All','registration','All','All','ACCESS_EDIT');
    xarRegisterMask('AdminRegistration','All','registration','All','All','ACCESS_ADMIN');

/** --------------------------------------------------------
 * Set up privileges
 */
    xarRegisterPrivilege('AdminRegistration','All','registration','All','All','ACCESS_ADMIN','Admin the Registration module');
    xarRegisterPrivilege('ViewRegistrationLogin','All','blocks','Block','registration:rlogin:All','ACCESS_OVERVIEW','View the User Access block');
    xarRegisterPrivilege('ViewRegistration','All','registration','All','All','ACCESS_OVERVIEW','View the User Access block');

/** --------------------------------------------------------
 * Define modvars
 */
    xarModSetVar('registration', 'allowregistration', false);
    xarModSetVar('registration', 'requirevalidation', true);
    xarModSetVar('registration', 'uniqueemail', true); //move back to roles - better there
    xarModSetVar('registration', 'askwelcomeemail', true);
    xarModSetVar('registration', 'askvalidationemail', true); // not in reg atm, leave in roles?
    xarModSetVar('registration', 'askdeactivationemail', true);// not in reg atm, leave in roles?
    xarModSetVar('registration', 'askpendingemail', true); // not in reg atm, leave in roles?
    xarModSetVar('registration', 'askpasswordemail', true);// not in reg atm, leave in roles?
    //xarModSetVar('registration', 'defaultgroup', 'Users'); //Use the Roles modvar
    xarModSetVar('registration', 'minage', 13);

    //we need these too
    xarModSetVar('registration', 'SupportShortURLs', false);
    xarModSetVar('registration', 'showterms', true);
    xarModSetVar('registration', 'showprivacy', true);
    xarModSetVar('registration', 'chooseownpassword', false);
    xarModSetVar('registration', 'notifyemail', xarModGetVar('mail', 'adminmail'));
    xarModSetVar('registration', 'sendnotice', false);
    xarModSetVar('registration', 'explicitapproval', true);
    xarModSetVar('registration', 'showdynamic', false);
    xarModSetVar('registration', 'sendwelcomeemail', false);
    xarModSetVar('registration', 'minpasslength', 5);
    $defaultregmodule= xarModGetVar('roles','defaultregmodule');
    if (!isset($defaultregmodule)) {
        xarModSetVar('roles','defaultregmodule',xarModGetIDFromName('registration'));
    }

/** ---------------------------------------------------------------
 * Set disallowed names
 */
    $names = 'Admin
Root
Linux';
    $disallowednames = serialize($names);
    xarModSetVar('registration', 'disallowednames', $disallowednames);

/* This really has to be in roles as a user can modify their email after registration
    $emails = 'none@none.com
president@whitehouse.gov';
    $disallowedemails = serialize($emails);
    xarModSetVar('registration', 'disallowedemails', $disallowedemails);
*/
/** ---------------------------------------------------------------
 * Set disallowed IPs
 */
    $ips = '';
    $disallowedips = serialize($ips);
    xarModSetVar('registration', 'disallowedips', $disallowedips);
   // Register blocks - same as authsystem but has a registration link
    $tid = xarModAPIFunc('blocks',
            'admin',
            'register_block_type',
            array('modName' => 'registration',
                'blockType' => 'rlogin'));
    if (!$tid) return;

    /* This init function brings our module to version 1.0.0, run the upgrades for the rest of the initialisation */
    return registration_upgrade('1.0.1');
}

function registration_activate()
{
    return true;
}

/**
 * Upgrade the registration module from an old version
 *
 * @access public
 * @param oldVersion $
 * @returns bool
 */
function registration_upgrade($oldVersion)
{
    // Upgrade dependent on old version number
    switch ($oldVersion) {
        case '1.0.0':
        //set new vars
        xarModSetVar('registration', 'notifyemail', xarModGetVar('mail', 'adminmail'));

        //delete old vars
        xarModDelVar('registration', 'lockouttime'); // to authsystem
        xarModDelVar('registration', 'lockouttries'); // to authsystem
        xarModDelVar('registration', 'uselockout'); // to authsystem
        $defaultregmodule= xarModGetVar('roles','defaultregmodule');
        if (!isset($defaultregmodule)) {
            xarModSetVar('roles','defaultregmodule', xarModGetIDFromName('registration'));
        }
        case '1.0.1':
            $defaultregmodule= xarModGetVar('roles','defaultregmodule');
            if (!isset($defaultregmodule) || $defaultregmodule < 1) {
                xarModSetVar('roles','defaultregmodule', xarModGetIDFromName('registration'));
            }
        case '1.0.2':
            if (!isset($requiredisplayname)) {
                xarModSetVar('registration','requiredisplayname',true);
            }
        case '1.0.3':
            /* Define instances for registration blocks  */
            $dbconn = xarDBGetConn();
            $xartable = xarDBGetTables();
            $blockinstancetable =xarDBGetSiteTablePrefix() . '_block_instances';
            $blocktypestable = xarDBGetSiteTablePrefix() . '_block_types';

            $query = "SELECT DISTINCT xar_name FROM $blockinstancetable
                as instances LEFT JOIN  $blocktypestable
                as btypes ON btypes.xar_id = instances.xar_type_id WHERE xar_module = 'registration'";
            $instances = array(
                        array('header' => 'Registration Block Name:',
                                'query' => $query,
                                'limit' => 20
                            )
                    );
            xarDefineInstance('registration','Block',$instances);
        case '1.0.4':
             //remove instances from modules where block instances only are created
            //the block security rework is complete (xarigami 1.1.4)
             xarRemoveInstances('registration','Block');
             xarModDelVar('registration', 'minpasslength');
             xarModDelVar('registration', 'disallowedips');
             xarModDelVar('registration', 'disallowednames');
             xarModDelVar('registration','requiredisplayname'); //this is a 'role' property and should be in roles
        case '1.0.5':
            xarModSetVar('registration','repeatemail',false);
        case '1.1.0':
            //update version number to reflect exception work for cirrus
        case '1.1.1':
            //update version to reflect slight change to requiredisplay name logic -display name is not shown/required at all unless chosen
        case '1.1.2':
              xarModSetVar('registration', 'aliasname', '');
              xarModSetVar('registration', 'useModuleAlias', FALSE);
        case '1.2.0':
              xarModSetVar('registration', 'hidemoduleurl', FALSE);
         case '1.2.1': //to signify checkage updates and custom pages for terms and privacy
             xarModSetVar('registration', 'customterms', '');
             xarModSetVar('registration', 'customprivacy', '');
        case '1.2.2'://current version

        break;
    }
    // Update successful
    return true;
}

/**
 * Delete the registration module
 *
 * @access public
 * @param none $
 * @returns bool
 */
function registration_delete()
{
   // UnRegister blocks
    if (!xarModAPIFunc('blocks', 'admin', 'unregister_block_type',
                       array('modName'  => 'registration',
                             'blockType'=> 'rlogin'))) return;

    //check if the roles default registration module is set
    //If so - we have to remove the registration value if it's registration module
    $regid = xarModGetIDFromName('registration');
    $defaultregvalue = xarModGetVar('roles','defaultregmodule');
    if (isset($defaultregmodule) && $defaultregmodule==$regid) {
        xarModSetVar('roles','defaultregmodule',NULL);
    }

    /**
     * Remove modvars, instances, masks and privs
     */
    xarModDelAllVars('registration');
    xarRemoveMasks('registration');
    xarRemoveInstances('registration');
    xarRemovePrivileges('registration');
    // Deletion successful
    return true;
}

?>