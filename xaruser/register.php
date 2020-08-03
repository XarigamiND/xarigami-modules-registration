<?php
/**
 * Register a new user
 *
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Let a new user register
 *
 * Multiple steps to create a new user, as follows:
 *  - get user to agree to terms and conditions (if required)
 *  - get initial information from user
 *  - send confirmation email to user (if required)
 *  - obtain confirmation response from user
 *  - obtain administration permission for account (if required)
 *  - activate account
 *  - send welcome email (if required)
 *
 * @param string phase The phase we are in. Each phase has an extra set of params
                        choices
                        checkage
                        registerform (DEFAULT)
                        checkregistration
                        createuser
 * @return array
 */
function registration_user_register()
{
    // Security check
    if (!xarSecurityCheck('ViewRegistration')) return xarResponseForbidden();

    //If a user is already logged in, no reason to see this.
    //We are going to send them to their account.
    if (xarUserIsLoggedIn()) {
        xarResponseRedirect(xarModURL('registration', 'user', 'terms'));
       return true;
    }
    $allowregistration = xarModGetVar('registration', 'allowregistration');
    if ($allowregistration != true) {
        return xarTplModule('registration','user','errors',array('errortype' => 'registration_suspended'));

    }

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

    //we could turn off registration, but let's check for site lock .
    //We don't want people  registering during this period
    $lockvars = unserialize(xarModGetVar('roles','lockdata'));
    if ($lockvars['locked'] ==1 ) {
         return xarTplModule('authsystem','user','errors',array('errortype' => 'site_locked', 'var1'  => $lockvars['message']));
     }

    //common vars
    $repeatemail        = xarModGetVar('registration', 'repeatemail');
    $chooseownpassword  = xarModGetVar('registration', 'chooseownpassword');
    $minpasslength      = xarModGetVar('registration','minpasslength');
    $showterms          = xarModGetVar('registration','showterms');
    $showprivacy        = xarModGetVar('registration','showprivacy');
    $requiredisplayname = xarModGetVar('roles','requiredisplayname');
    $minage             = xarModGetVar('registration','minage');
    xarTplSetPageTitle(xarML('New Account'));

    $checkageoptions = array( 1 =>xarML('I am #(1) or older',$minage),
                              0 =>xarML('I am NOT #(1) or older',$minage)
                            );
    if (!xarVarFetch('phase','str:1:100',$phase,'request',XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('checkage','str',$checkage,FALSE,XARVAR_NOT_REQUIRED)) return;

    switch(strtolower($phase)) {

        case 'choices':
            xarTplSetPageTitle(xarML('Log In'));
            $loginlabel = xarML('Sign In');
            $data       = xarTplModule('authsystem','user', 'showloginform', array('loginlabel' => $loginlabel));
            break;

        case 'checkage':
            $submitlink = xarModURL('registration', 'user', 'register',array('phase' => 'registerform'));
            $data       = xarTplModule('registration','user', 'checkage',
                                     array('minage'     => $minage,
                                           'submitlink' => $submitlink,
                                           'checkage' => FALSE,
                                           'authid' => xarSecGenAuthKey(),
                                           'menulinks'  => xarModAPIFunc('registration','user','getmenulinks'),
                                           'checkageoptions' =>$checkageoptions
                                           )
                                       );
            break;

        case 'registerform': //Make this default now login is handled by authsystem
        default:


             if ($minage > 0) {
                //we first need to check  minage has been checked
                if ($checkage == FALSE) {
                     $data       = xarTplModule('registration','user', 'checkage',
                                     array('minage'     => $minage,
                                           'notminage'  => 1,
                                           'menulinks'  => xarModAPIFunc('registration','user','getmenulinks'),
                                            'checkageoptions' =>$checkageoptions
                                           )
                                       );
               return $data;
                }

             }
            // authorisation code
            $authid = xarSecGenAuthKey();
            $redirecturl = isset($redirecturl)?$redirecturl:'';
            $uservalues = xarSessionGetVar('regdata');
            // current values (none)
            //if the referrer is coming back from the check page, let's load up the known values for them to edit
            if (xarRequestIsLocalReferer() && stristr(xarServerGetVar('HTTP_REFERER'),'register')) {
                $username = isset($uservalues['username'])?$uservalues['username']: '';
                $displayname = isset($uservalues['displayname'])?$uservalues['displayname']: '';
                $email = isset($uservalues['email'])?$uservalues['email']: '';
            } else {
                $username = '';
                $displayname = '';
                $email = '';
            }
            //destroy the session var
            xarSessionDelVar('regdata');
            $values = array('username'    => $username,
                            'displayname' => $displayname,
                            'email'       => $email,
                            'email2'      => $email,
                            'pass1'       => '',
                            'pass2'       => ''
                            );

            // invalid fields (none)
            $invalid = array();
            $antibotinvalid = null;
            // dynamic properties (if any)

            $properties = null;
            $withupload = (int) FALSE;
            if (xarModIsHooked('dynamicdata','roles')) {
                // get the Dynamic Object defined for this module (and itemtype, if relevant)
                $object = xarModAPIFunc('dynamicdata','user','getobject',
                                         array('module' => 'roles'));
                if (isset($object) && !empty($object->objectid)) {
                    // get the Dynamic Properties of this object
                    $properties = $object->getProperties();
                }
                if (isset($properties)) {
                    foreach ($properties as $key => $prop) {
                        if (isset($prop->upload) && $prop->upload == TRUE) {
                            $withupload = (int) TRUE;
                        }
                    }
                }
            }

            /* Call hooks here, others than just dyn data
             * We pass the phase in here to tell the hook it should check the data
             */
            $item['module'] = 'registration';
            $item['itemid'] = 0;
            $item['values'] = $values;
            $item['phase']  = $phase;
            $hooks = xarModCallHooks('item', 'new', '', $item);

            if (empty($hooks)) {
                $hookoutput = array();
            } else {
                /* You can use the output from individual hooks in your template too, e.g. with
                 * $hookoutput['categories'], $hookoutput['dynamicdata'], $hookoutput['keywords'] etc.
                 */
                $hookoutput = $hooks;
            }

            $data = xarTplModule('registration','user', 'registerform',
                           array('authid'     => $authid,
                                 'values'     => $values,
                                 'invalid'    => $invalid,
                                 'properties' => $properties,
                                 'hookoutput' => $hookoutput,
                                 'requiredisplayname'=> $requiredisplayname,
                                 'antibotinvalid' => $antibotinvalid,
                                 'withupload'     => isset($withupload) ? $withupload : (int) FALSE,
                                 'userlabel'      => xarML('New User'),
                                 'repeatemail'      => $repeatemail,
                                 'chooseownpassword' => $chooseownpassword,
                                 'minpasslength'     => $minpasslength,
                                 'showterms'         => $showterms,
                                 'showprivacy'       => $showprivacy,
                                 'redirecturl'  => $redirecturl,
                                 'menulinks'    => xarModAPIFunc('registration','user','getmenulinks')
                                 )
                                 );
            break;

        case 'checkregistration':

            if (!xarVarFetch('username',     'str:1:100', $username,     '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('displayname',  'str:1:100', $displayname,  '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pass1',        'str:4:100', $pass1,        '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pass2',        'str:4:100', $pass2,        '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('email',        'str:1:100', $email,        '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('email2',       'str:1:100', $email2,       '',    XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('agreetoterms', 'checkbox',  $agreetoterms, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('redirecturl',  'str:0:255',  $redirecturl, '', XARVAR_NOT_REQUIRED)) return;
            // Confirm authorisation code.
            if (!xarSecConfirmAuthKey()) return;
            $antibotinvalid = FALSE; //initialize
            if (empty($redirecturl)) $redirecturl = xarModURL('registration','user','main');
            $ip = xarSessionGetIPAddress();//xarServerGetVar('REMOTE_ADDR');
            $invalid = xarModApiFunc('roles','user','validatevar', array('type'=>'ip', 'var'=>$ip));
            if (!empty($invalid)) {
                 return xarTplModule('registration','user','errors',array('errortype' => 'bad_ip'));
                 //throw new BadParameterException(null,$invalid);
            }

            // current values (in case some field is invalid, we'll return to the previous template)
            // Pass back all values again so the user only has to type in incorrect values that are highlighted
            $values = array('username'    => $username,
                            'displayname' => $displayname,
                            'email'       => $email,
                            'email2'      => $email2,
                            'pass1'       => $pass1,
                            'pass2'       => $pass2);

            // invalid fields (we'll check this below)
            $invalid = array(); //initialize var
            //let's check for some submit hooks for captcha
            $submithook = xarModCallHooks('item', 'submit', 0, array('itemtype'=>0));
            $antibotinvalid = isset($submithook['antibotinvalid']) ? $submithook['antibotinvalid'] : 0;
            if ($antibotinvalid)  $invalid['antibotinvalid'] = 1;
            /* Call hooks here, others than just dyn data
             * We pass the phase in here to tell the hook it should check the data
             */
            $item = array();
            $item['module'] = 'registration';
            $item['itemid'] = 0;
            $item['values'] = $values; // TODO: this includes the password. Do we want this?
            $item['phase']  = $phase;
            $item['antibotinvalid'] = $antibotinvalid;
            $hooks = xarModCallHooks('item', 'new','', $item);

            if (empty($hooks)) {
                $hookoutput = array();
            } else {
                 $hookoutput = $hooks;
            }

            // check username
            $invalid['username'] = xarModApiFunc('roles','user','validatevar', array('type'=>'username', 'var'=>$username));

            // check display name if required

            if ($requiredisplayname == TRUE) {
                $invalid['displayname'] = xarModApiFunc('roles','user','validatevar', array('type'=>'displayname', 'var'=>$displayname));
            } elseif (empty($displayname)) {
                $displayname= $username; //only set this if it's not set already
            }

            // check email
            $invalid['email'] = xarModApiFunc('roles','user','validatevar', array('type'=>'email', 'var'=>$email));

            if (xarModGetVar('registration','repeatemail')) {
                $invalid['email2'] = xarModApiFunc('roles','user','validatevar', array('type'=>'email2', 'var'=>array($email,$email2)));
                if(!empty($invalid['email']) || !empty($invalid['email2'])){
                    // null out if either email is invalid
                    $values['email2'] = '';
                }
                if($invalid['email2'] == ''){
                    unset($invalid['email2']);
                }
            }
            // agree to terms (kind of dumb, but for completeness)
            $invalid['agreetoterms'] = xarModApiFunc('roles','user','validatevar', array('type'=>'agreetoterms', 'var'=>$agreetoterms));

            // Check password and set
            $pass = '';
            if (xarModGetVar('registration', 'chooseownpassword')) {
                $invalid['pass1'] = xarModApiFunc('roles','user','validatevar', array('type'=>'pass1', 'var'=>$pass1 ));
                if (empty($invalid['pass1'])) {
                    $invalid['pass2'] = xarModApiFunc('roles','user','validatevar', array('type'=>'pass2', 'var'=>array($pass1,$pass2) ));
                }
                if (empty($invalid['pass1']) && empty($invalid['pass2']))   {
                    $pass = $pass1;
                }
            }

            // dynamic properties
            $checkdynamic = xarModGetVar('registration', 'showdynamic');

            //grab any properties - show or hide in the template accordingly

            // dynamic properties (if any)
            $properties = null;
            $isvalid = true;
            //holds the property values so we can use them easily in the templates
            $propertyvalues = array();
            if (xarModIsHooked('dynamicdata','roles') ) {
                // get the Dynamic Object defined for this module (and itemtype, if relevant)
                $object = xarModAPIFunc('dynamicdata','user','getobject',
                                          array('module' => 'roles'));
                if (isset($object) && !empty($object->objectid)) {

                    // check the input values for this object !
                    //only check if we are showing the dd properties on registration
                    if ($checkdynamic) {
                    $isvalid = $object->checkInput();
                    }
                    // get the Dynamic Properties of this object
                    $properties = $object->getProperties();
                    foreach ($properties as $name=>$property) {
                     $propertyvalues[$name] = $property->value;
                     }
                }
            } else {
               $properties = array();
            }

            // new authorisation code
            $authid = xarSecGenAuthKey();

            // check if any of the fields (or dynamic properties) were invalid
            $a = array_count_values($invalid); // $a[''] will be the count of null values
            if (!isset($a[''])) $a['']='';
            $countInvalid = count($invalid) - $a[''];
            $userdata =  array('username'    => $username,
                               'email'       => $email,
                               'displayname' => $displayname,
                               'propertyvalues'=>$propertyvalues);
            //set user data in a session var but not password
             xarSessionSetVar('regdata',$userdata);

            if ($countInvalid > 0 || !$isvalid) {
                // if so, return to the previous template
                return xarTplModule('registration','user', 'registerform',
                                 array('authid'      => $authid,
                                       'values'      => $values,
                                       'invalid'     => $invalid,
                                       'properties'  => $properties,
                                       'propertyvalues'=>$propertyvalues,
                                       'hookoutput'  => $hookoutput,
                                       'requiredisplayname' => $requiredisplayname,
                                       'antibotinvalid' => $antibotinvalid,
                                       'createlabel' => xarML('Create Account'),
                                       'userlabel'   => xarML('New User'),
                                       'repeatemail'      => $repeatemail,
                                       'chooseownpassword' => $chooseownpassword,
                                       'minpasslength'     => $minpasslength,
                                       'showterms'         => $showterms,
                                       'showprivacy'       => $showprivacy,
                                       'redirecturl' => $redirecturl,
                                       'menulinks'    => xarModAPIFunc('registration','user','getmenulinks')
                                       )
                                       );
            }
            $hookoutput['formantibot'] = ''; //we don't want formantibot again in the confirmation form - just one step is ok
            // everything seems OK -> go on to the next step



            $data = xarTplModule('registration','user', 'confirmregistration',
                                 array('username'    => $username,
                                       'email'       => $email,
                                       'displayname' => $displayname,
                                       'pass'        => $pass,
                                       'ip'          => $ip,
                                       'authid'      => $authid,
                                       'redirecturl' => $redirecturl,
                                       'properties'  => $properties,
                                       'propertyvalues'=>$propertyvalues,
                                       'hookoutput'  => $hookoutput,
                                       'requiredisplayname' => $requiredisplayname,
                                       'repeatemail'      => $repeatemail,
                                       'chooseownpassword' => $chooseownpassword,
                                       'minpasslength'     => $minpasslength,
                                       'showterms'         => $showterms,
                                       'showprivacy'       => $showprivacy,
                                       'createlabel' => xarML('Create Account'),
                                       'menulinks'    => xarModAPIFunc('registration','user','getmenulinks')
                                       )
                                       );

            break;

        case 'createuser':
            if (!xarVarFetch('username',  'str:1:100', $username, '', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('displayname',  'str:1:100', $displayname, '', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pass',      'str:4:100', $pass,     '', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('ip',        'str:4:100', $ip,       '', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('email',     'str:1:100', $email,    '', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('redirecturl',  'str:0:255',  $redirecturl, '', XARVAR_NOT_REQUIRED)) return;

            //Set some general vars that we need in various registration options
            $pending = xarModGetVar('registration', 'explicitapproval'); //Require admin approval for account
            $requireValidation = xarModGetVar('registration', 'requirevalidation'); //require user validation of account by email
            $chooseownpassword = xarModGetVar('registration', 'chooseownpassword'); //user can choose their own password
            $activationemailonly = !$chooseownpassword && !$requireValidation;
            //we can allow registration without displayname but the field must be populated for various roles functions
            if (empty($displayname)) $displayname = $username;

            //Get the default auth module data
            $loginlink =xarModURL('authsystem','user','main');

            //variables required for display of correct validation template to users, depending on registration options
            $tplvars = array();
            $tplvars['loginlink'] = $loginlink;
            $tplvars['pending']   = $pending;


            // Confirm authorisation code.
            if (!xarSecConfirmAuthKey()) return;

            // determine state of this create user
            $state = xarModAPIFunc('registration','user','createstate' );

            // need a password
            if (empty($pass)){
                $pass = xarModAPIFunc('roles', 'user', 'makepass');
            }

            // Create confirmation code if required
            if ($requireValidation) {
                $confcode = xarModAPIFunc('roles', 'user', 'makepass');
            } else {
                $confcode ='';
            }
            //create user account creation time
            $now = time();

            $properties = array();
           // if (xarModIsHooked('dynamicdata','roles')) {
                    // get the Dynamic Object defined for this module (and itemtype, if relevant)
                    $object = xarModAPIFunc('dynamicdata','user','getobject',
                                              array('module' => 'roles'));
                    if (isset($object) && !empty($object->objectid)) {

                        // check the input values for this object !
                        //$isvalid = $object->checkInput();

                        // get the Dynamic Properties of this object
                        $properties = $object->getProperties();
                     }
         //   }
            //destroy the user session var
            xarSessionDelVar('regdata');
            //Create the user
            $userdata = array('uname'  => $username,
                              'displayname' => $displayname,
                              'email'    => $email,
                              'pass'     => $pass,
                              'date'     => $now,
                              'valcode'  => $confcode,
                              'state'    => $state);

            $uid = xarModAPIFunc('roles', 'admin', 'create', $userdata);


            if ($uid == 0) return;

            //Make sure the user email setting is off unless the user sets it
            xarModSetUserVar('roles','usersendemails', false, $uid);


             // Option: If admin requires notification of a new user, and no validation required,
             // send out an email to Admin

             // Insert the user into the default users group
             $userRole = xarModGetVar('roles', 'defaultgroup');

             // Get the group id
             $defaultRole = xarModAPIFunc('roles', 'user', 'get', array('name'  => $userRole,'type' => 1));

             if (empty($defaultRole)) return;
                // Make the user a member of the users role
             if(!xarMakeRoleMemberByID($uid, $defaultRole['uid'])) return;
             xarModSetVar('roles', 'lastuser', $uid);

            //call hooks here
             $userdata['module'] = 'registration';
             $userdata['itemid'] = $uid;
             xarModCallHooks('item', 'create', $uid, $userdata);

            // Let's finish by sending emails to those that require it based on options - the user or the admin
            // and redirecting to appropriate pages that depend on user state and options set in the registration config
            // note: dont email password if user chose his own (should this condition be in the createnotify api instead?)
            $ret = xarModApiFunc('registration','user','createnotify',
                array(  'username'  => $username,
                        'displayname'  => $displayname,
                        'email'     => $email,
                        'pass'      => $chooseownpassword ? '' : $pass,
                        'uid'       => $uid,
                        'ip'        => $ip,
                        'state'     => $state,
                        'menulinks'    => xarModAPIFunc('registration','user','getmenulinks')
                        )
                         );
            if (!$ret) return;


            // go to appropriate page, based on state
            if ($state==ROLES_STATE_ACTIVE) {
                $loginlink = xarModURL('authsystem','user','main');


                //let's log them in and send to their account page if no other redirect is in force
                    $firstlogin = xarModGetVar('roles','firstloginurl')?xarModGetVar('roles','firstloginurl'):'';
                    $redirecthomepage = xarModGetVar('roles','loginredirect')? xarModGetVar('roles','loginredirect'):0;
                    $data= array();
                    $noredirects = empty($firstlogin) && empty($redirecthomepage);
                    $redirected = array();
                     // log in first  and then redirect appropriately
                    xarModAPIFunc('authsystem', 'user', 'login',
                    array(  'uname'      => $username,
                            'pass'       => $pass,
                            'rememberme' => 0));
                    xarModSetUserVar('roles','userlastlogin',time()); //this is what everyone else will see
                    $redirect = xarModUrl('roles', 'user', 'account');
                    xarSessionSetVar('roles_firstlogin',true);
                    if (!$noredirects) { //we need to find out what they are
                        $redirect = xarModAPIFunc('authsystem','user','checkredirects',array('uname'=>$username,'lastresort'=>false));
                    }

                    $time = '4';
                    xarVarSetCached('Meta.refresh','url', $redirect);
                    xarVarSetCached('Meta.refresh','time', $time);
                    $data = xarTplModule('registration','user', 'confirmlogin', array('loginlink'=>$loginlink,'noredirects'=>$noredirects));

                break;

            } else if ($state==ROLES_STATE_PENDING) {
                // If we are still waiting on admin to review pending accounts send the user to a page to notify them
                // This page is for options of validation alone, validation and pending, and pending alone
                $data = xarTplModule('roles','user', 'getvalidation', $tplvars);

            } else { // $state==ROLES_STATE_NOTVALIDATED
                $data = xarTplModule('registration','user', 'waitingconfirm');
            }

            break;
    }

    return $data;
}
?>
