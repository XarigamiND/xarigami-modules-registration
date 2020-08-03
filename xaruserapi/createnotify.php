<?php
/**
 * create notify- send out email notifications during user create based on state
 *
 * @package Xaraya
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com
 *
 * @subpackage Xarigami Registration Module
 * @copyright (C) 2006-2011 2skies.com
 * @link http://xarigami.com/project/registration
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * @access public
 * @author Jonathan Linowes
 * @author jojodee
 * @author Damien Bonvillain
 * @author Gregor J. Rothfuss
 * @param 'username'
 * @param 'displayname'
 * @param 'email'
 * @param 'pass'  password
 * @param 'uid'  user id
 * @param 'ip'  user ip (optional)
 * @param 'state'  one of ROLES_STATE_NOTVALIDATED, ROLES_STATE_PENDING, ROLES_STATE_ACTIVE
 * @return true if ok
 */
function registration_userapi_createnotify($args)
{
    extract($args);

    $chooseownpass = xarModGetVar('registration', 'chooseownpassword');
    $requirevalidation = xarModGetVar('registration', 'requirevalidation');
    $activationemailonly = !$chooseownpass && !$requirevalidation;

    if (($state==ROLES_STATE_NOTVALIDATED)  ){
        if (empty($ip)) {
            $ip = xarSessionGetIPAddress();//xarServerGetVar('REMOTE_ADDR');
        }

        // TODO: make sending mail configurable too, depending on the other options ?
        $emailargs = array( 'uid'           => array($uid => 1), //we want to use values passed in not those that might be retrieved from user record
                            'mailtype'      => 'confirmation',
                            'ip'            => $ip,
                            'pass'          => $pass );

        if (!xarModAPIFunc('roles', 'admin', 'senduseremail', $emailargs)) {
            return xarTplModule('registration','user','errors',array('errortype' => 'mail_error', 'var1'=>'confirmation'));

        }
    }

    if ($state==ROLES_STATE_PENDING || $state==ROLES_STATE_ACTIVE) {
        // Send an e-mail to the admin if notification of new user registration is required,
        // Same  email is added to the 'getvalidation' new users in Roles module

        if (xarModGetVar('registration', 'sendnotice')) {
            $terms= '';
            if (xarModGetVar('registration', 'showterms') == 1) {
                // User has agreed to the terms and conditions.
                $terms = xarML('This user has agreed to the site terms and conditions.');
            }

            $emailargs = array(
                            'adminname'     => xarModGetVar('mail', 'adminname'),
                            'adminemail'    => xarModGetVar('registration', 'notifyemail'),
                            'userdisplayname'  => $displayname,
                            'username'      => $username,
                            'useremail'     => $email,
                            'terms'         => $terms,
                            'uid'           => $uid,
                            'userstatus'    => $state );

            if (!xarModAPIFunc('registration', 'user', 'notifyadmin', $emailargs)) {
               return; // TODO ...something here if the email is not sent..
            }
        }
    }

    if ($state==ROLES_STATE_ACTIVE)  {
         // send welcome email to user(option)
         // This template is used in options for user validation, user validation and user pending, and user pending alone
        if (xarModGetVar('registration', 'sendwelcomeemail') || ($activationemailonly) ){
            $emailargs = array(
                            'uid'      => array($uid => 1),
                            'mailtype' => 'welcome',
                            'ip'        =>$ip,
                            'pass'      => $activationemailonly ?$pass:''
                            );

            if (!xarModAPIFunc('roles',  'admin', 'senduseremail', $emailargs)) {
                 return xarTplModule('registration','user','errors',array('errortype' => 'mail_error', 'var1'=>'welcome'));
            }
        }
    }

    return true;
}
?>