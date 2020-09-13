<?php
/**
 * General functions file for the plugin.
 *
 * @package    user_roles
 * @subpackage Includes
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

/**
 * Validates a value as a boolean.  This way, strings such as "true" or "false" will be converted
 * to their correct boolean values.
 *
 * @since  1.0.0
 * @access public
 * @param  mixed   $val
 * @return bool
 */
function user_roles_validate_boolean( $val ) {

	return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
}


/**
 * Helper function for sorting objects by priority.
 *
 * @since  2.0.0
 * @access protected
 * @param  object     $a
 * @param  object     $b
 * @return int
 */
function user_roles_priority_sort( $a, $b ) {

	return $a->priority - $b->priority;
}
