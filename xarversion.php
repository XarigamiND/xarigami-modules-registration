<?php
/**
 * Registration module initialization
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

$modversion['name']           = 'registration';
$modversion['directory']      = 'registration';
$modversion['id']             = '30205';
$modversion['version']        = '1.2.2';
$modversion['displayname']    = 'Member registration';
$modversion['description']    = 'Enable users to register as members on your site and provision of membership information.';
$modversion['credits']        = 'xardocs/credits.txt';
$modversion['help']           = 'xardocs/help.txt';
$modversion['changelog']      = 'xardocs/changelog.txt';
$modversion['license']        = 'xardocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Xarigami Team';
$modversion['contact']        = 'http://xarigami.com';
$modversion['homepage']       = 'http://xarigami.com/project/xarigami_registration';
$modversion['admin']          = 1;
$modversion['user']           = 1;
$modversion['class']          = 'Registration';
$modversion['category']       = 'Users & Groups';
$modversion['dependency']     = array(27,42);
$modversion['dependencyinfo']   = array(
                                    0 => array(
                                            'name' => 'core',
                                            'version_ge' => '1.4.0'
                                         ),
                                    27 => array(
                                            'name' => 'roles',
                                            'version_ge' => '1.2.0'
                                        ),
                                    42 => array(
                                            'name' => 'authsystem',
                                            'version_ge' => '1.0.0'
                                        ),
                                );

if (false) { //Load and translate once
    xarML('Member Registration');
    xarML('Enable users to register as members on your site');
}
?>