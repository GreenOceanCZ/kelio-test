<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Validation utilities.
 */
class Validators extends Nette\Object
{
	protected static $validators = array(
		'bool' => 'is_bool',
		'boolean' => 'is_bool',
		'int' => 'is_int',
		'integer' => 'is_int',
		'float' => 'is_float',
		'number' => NULL, // is_int || is_float,
		'numeric' => array(__CLASS__, 'isNumeric'),
		'numericint' => array(__CLASS__, 'isNumericInt'),
		'string' => 'is_string',
		'unicode' => array(__CLASS__, 'isUnicode'),
		'array' => 'is_array',
		'list' => array('Nette\Utils\Arrays', 'isList'),
		'object' => 'is_object',
		'resource' => 'is_resource',
		'scalar' => 'is_scalar',
		'callable' => array(__CLASS__, 'isCallable'),
		'null' => 'is_null',
		'email' => array(__CLASS__, 'isEmail'),
		'url' => array(__CLASS__, 'isUrl'),
		'uri' => array(__CLASS__, 'isUri'),
		'none' => array(__CLASS__, 'isNone'),
		'type' => array(__CLASS__, 'isType'),
		'identifier' => array(__CLASS__, 'isPhpIdentifier'),
		'pattern' => NULL,
		'alnum' => 'ctype_alnum',
		'alpha' => 'ctype_alpha',
		'digit' => 'ctype_digit',
		'lower' => 'ctype_lower',
		'upper' => 'ctype_upper',
		'space' => 'ctype_space',
		'xdigit' => 'ctype_xdigit',
	);

	protected static $counters = array(
		'string' => 'strlen',
		'unicode' => array('Nette\Utils\Strings', 'length'),
		'array' => 'count',
		'list' => 'count',
		'alnum' => 'strlen',
		'alpha' => 'strlen',
		'digit' => 'strlen',
		'lower' => 'strlen',
		'space' => 'strlen',
		'upper' => 'strlen',
		'xdigit' => 'strlen',
	);


	/**
	 * Throws exception if a variable is of unexpected type.
	 * @param  mixed
	 * @param  string  expected types separated by pipe
	 * @param  string  label
	 * @return void
	 */
	public static function assert($value, $expected, $label = 'variable')
	{
		if (!static::is($value, $expected)) {
			$expected = str_replace(array('|', ':'), array(' or ', ' in range '), $expected);
			if (is_array($value)) {
				$type = 'array(' . count($value) . ')';
			} elseif (is_object($value)) {
				$type = 'object ' . get_class($value);
			} elseif (is_string($value) && strlen($value) < 40) {
				$type = "string '$value'";
			} else {
				$type = gettype($value);
			}
			throw new AssertionException("The $label expects to be $expected, $type given.");
		}
	}


	/**
	 * Throws exception if an array field is missing or of unexpected type.
	 * @param  array
	 * @param  string  item
	 * @param  string  expected types separated by pipe
	 * @param  string
	 * @return void
	 */
	public static function assertField($arr, $field, $expected = NULL, $label = "item '%' in array")
	{
		self::assert($arr, 'array', 'first argument');
		if (!array_key_exists($field, $arr)) {
			throw new AssertionException('Missing ' . str_replace('%', $field, $label) . '.');

		} elseif ($expected) {
			static::assert($arr[$field], $expected, str_replace('%', $field, $label));
		}
	}


	/**
	 * Finds whether a variable is of expected type.
	 * @param  mixed
	 * @param  string  expected types separated by pipe with optional ranges
	 * @return bool
	 */
	public static function is($value, $expected)
	{
		foreach (explode('|', $expected) as $item) {
			list($type) = $item = explode(':', $item, 2);
			if (isset(static::$validators[$type])) {
				if (!call_user_func(static::$validators[$type], $value)) {
					continue;
				}
			} elseif ($type === 'number') {
				if (!is_int($value) && !is_float($value)) {
					continue;
				}
			} elseif ($type === 'pattern') {
				if (preg_match('|^' . (isset($item[1]) ? $item[1] : '') . '\z|', $value)) {
					return TRUE;
				}
				continue;
			} elseif (!$value instanceof $type) {
				continue;
			}

			if (isset($item[1])) {
				if (isset(static::$counters[$type])) {
					$value = call_user_func(static::$counters[$type], $value);
				}
				$range = explode('..', $item[1]);
				if (!isset($range[1])) {
					$range[1] = $range[0];
				}
				if (($range[0] !== '' && $value < $range[0]) || ($range[1] !== '' && $value > $range[1])) {
					continue;
				}
			}
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * Finds whether a value is an integer.
	 * @return bool
	 */
	public static function isNumericInt($value)
	{
		return is_int($value) || is_string($value) && preg_match('#^-?[0-9]+\z#', $value);
	}


	/**
	 * Finds whether a string is a floating point number in decimal base.
	 * @return bool
	 */
	public static function isNumeric($value)
	{
		return is_float($value) || is_int($value) || is_string($value) && preg_match('#^-?[0-9]*[.]?[0-9]+\z#', $value);
	}


	/**
	 * Finds whether a value is a syntactically correct callback.
	 * @return bool
	 */
	public static function isCallable($value)
	{
		return $value && is_callable($value, TRUE);
	}


	/**
	 * Finds whether a value is an UTF-8 encoded string.
	 * @param  string
	 * @return bool
	 */
	public static function isUnicode($value)
	{
		return is_string($value) && preg_match('##u', $value);
	}


	/**
	 * Finds whether a value is "falsy".
	 * @return bool
	 */
	public static function isNone($value)
	{
		return $value == NULL; // intentionally ==
	}


	/**
	 * Finds whether a variable is a zero-based integer indexed array.
	 * @param  array
	 * @return bool
	 */
	public static function isList($value)
	{
		return Arrays::isList($value);
	}


	/**
	 * Is a value in specified range?
	 * @param  mixed
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function isInRange($value, $range)
	{
		return (!isset($range[0]) || $range[0] === '' || $value >= $range[0])
			&& (!isset($range[1]) || $range[1] === '' || $value <= $range[1]);
	}


	/**
	 * Finds whether a string is a valid email address.
	 * @param  string
	 * @return bool
	 */
	public static function isEmail($value)
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$alpha = "a-z\x80-\xFF"; // superset of IDN
		return (bool) preg_match("(^
			(\"([ !#-[\\]-~]*|\\\\[ -~])+\"|$atom+(\\.$atom+)*)  # quoted or unquoted
			@
			([0-9$alpha]([-0-9$alpha]{0,61}[0-9$alpha])?\\.)+    # domain - RFC 1034
			[$alpha]([-0-9$alpha]{0,17}[$alpha])?                # top domain
		\\z)ix", $value);
	}


	/**
	 * Finds whether a string is a valid http(s) URL.
	 * @param  string
	 * @return bool
	 */
	public static function isUrl($value)
	{
		$alpha = "a-z\x80-\xFF";
		return (bool) preg_match("(^
			https?://(
				(([-_0-9$alpha]+\\.)*                       # subdomain
					[0-9$alpha]([-0-9$alpha]{0,61}[0-9$alpha])?\\.)?  # domain
					[$alpha]([-0-9$alpha]{0,17}[$alpha])?   # top domain
				|\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}  # IPv4
				|\[[0-9a-f:]{3,39}\]                        # IPv6
			)(:\\d{1,5})?                                   # port
			(/\\S*)?                                        # path
		\\z)ix", $value);
	}


	/**
	 * Finds whether a string is a valid URI according to RFC 1738.
	 * @param  string
	 * @return bool
	 */
	public static function isUri($value)
	{
		return (bool) preg_match('#^[a-z\d+\.-]+:\S+\z#i', $value);
	}


	/**
	 * Checks whether the input is a class, interface or trait.
	 * @param  string
	 * @return bool
	 */
	public static function isType($type)
	{
		return class_exists($type) || interface_exists($type) || (PHP_VERSION_ID >= 50400 && trait_exists($type));
	}


	/**
	 * Checks whether the input is a valid PHP identifier.
	 * @return bool
	 */
	public static function isPhpIdentifier($value)
	{
		return is_string($value) && preg_match('#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\z#', $value);
	}

}
