<?php

// (c) Arnaud Ligot <spyroux@spyroux.be> 
// license : GPL (see Website of Obelisk)


// We need to implement the folowing function

/**
 * sessionStart - is called before anything append
 *
 * @global $db : a correct link to the database
 */
function sessionStart ()
{}

/**
 * isAuth - must return true if the current user is Authenticated
 *
 * @PRE: sessionStart is called
 * @global $db : a correct link to the database
 */
function isAuth()
{}

/**
 * get_uid - return the id of the current user into the database
 *
 * @PRE: isAuth = true
 * @global $db : a correct link to the database
 */
function get_uid()
{}

/**
 * auth - mark this user as autheticated
 *
 * @PRE: isAuth = false
 * @POST: isAuth = true during the session
 * @global $db : a correct link to the database
 */
function auth($uid)
{}

/**
 * logout - logout the current user
 *
 * @PRE: isAuth = true
 * @POST: isAuth = false during the session
 * @global $db : a correct link to the database
 */
function logout()
{}

/**
 * get_rightOn - return the right of the current user on the $action of 
 *			the $module
 *
 * @POST: isAuth = true ? right of the user : false
 * @global $db : a correct link to the database
 */
function get_rightOn($module, $action)
{}

/**
 * draw_loginForm - output HTML form in order to allow the login of the user
 */
function draw_loginForm()
{}

?>
