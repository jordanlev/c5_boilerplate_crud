<?php defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * Kohana-2.3.4's Validation library.
 *
 * $Id: Validation.php 4120 2009-03-25 19:22:31Z jheathco $
 *
 * @package    Validation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class KohanaValidation extends ArrayObject { //ArrayObject is a built-in PHP thing (it's an object that can also be treated like an array)

	// Filters
	protected $pre_filters = array();
	protected $post_filters = array();

	// Rules and callbacks
	protected $rules = array();
	protected $callbacks = array();

	// Rules that are allowed to run on empty fields
	protected $empty_rules = array('required', 'upload_required', 'matches');

	// Errors
	protected $errors = array();
	protected $messages = array();

	// Fields that are expected to be arrays
	protected $array_fields = array();

	// Checks if there is data to validate.
	protected $submitted;

	/**
	 * Creates a new KohanaValidation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  object
	 */
	public static function factory(array $array)
	{
		return new KohanaValidation($array);
	}

	/**
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		// The array is submitted if the array is not empty
		$this->submitted = ! empty($array);

		parent::__construct($array, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
	}

	/**
	 * Magic clone method, clears errors.
	 *
	 * @return  void
	 */
	public function __clone()
	{
		$this->errors = array();
	}

	/**
	 * Create a copy of the current validation rules and change the array.
	 *
	 * @chainable
	 * @param   array  new array to validate
	 * @return  Validation
	 */
	public function copy(array $array)
	{
		$copy = clone $this;

		$copy->exchangeArray($array);

		return $copy;
	}

	/**
	 * Test if the data has been submitted.
	 *
	 * @return  boolean
	 */
	public function submitted($value = NULL)
	{
		if (is_bool($value))
		{
			$this->submitted = $value;
		}

		return $this->submitted;
	}

	/**
	 * Returns an array of all the field names that have filters, rules, or callbacks.
	 *
	 * @return  array
	 */
	public function field_names()
	{
		// All the fields that are being validated
		$fields = array_keys(array_merge
		(
			$this->pre_filters,
			$this->rules,
			$this->callbacks,
			$this->post_filters
		));

		// Remove wildcard fields
		$fields = array_diff($fields, array('*'));

		return $fields;
	}

	/**
	 * Returns the array values of the current object.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Returns the ArrayObject values, removing all inputs without rules.
	 * To choose specific inputs, list the field name as arguments.
	 *
	 * @param   boolean  return only fields with filters, rules, and callbacks
	 * @return  array
	 */
	public function safe_array()
	{
		// Load choices
		$choices = func_get_args();
		$choices = empty($choices) ? NULL : array_combine($choices, $choices);

		// Get field names
		$fields = $this->field_names();

		$safe = array();
		foreach ($fields as $field)
		{
			if ($choices === NULL OR isset($choices[$field]))
			{
				if (isset($this[$field]))
				{
					$value = $this[$field];

					if (is_object($value))
					{
						// Convert the value back into an array
						$value = $value->getArrayCopy();
					}
				}
				else
				{
					// Even if the field is not in this array, it must be set
					$value = NULL;
				}

				// Add the field to the array
				$safe[$field] = $value;
			}
		}

		return $safe;
	}

	/**
	 * Add additional rules that will forced, even for empty fields. All arguments
	 * passed will be appended to the list.
	 *
	 * @chainable
	 * @param   string   rule name
	 * @return  object
	 */
	public function allow_empty_rules($rules)
	{
		// Any number of args are supported
		$rules = func_get_args();

		// Merge the allowed rules
		$this->empty_rules = array_merge($this->empty_rules, $rules);

		return $this;
	}

	/**
	 * Converts a filter, rule, or callback into a fully-qualified callback array.
	 *
	 * @return  mixed
	 */
	protected function callback($callback)
	{
		if (is_string($callback))
		{
			if (strpos($callback, '::') !== FALSE)
			{
				$callback = explode('::', $callback);
			}
			elseif (function_exists($callback))
			{
				// No need to check if the callback is a method
				$callback = $callback;
			}
			elseif (method_exists($this, $callback))
			{
				// The callback exists in KohanaValidation
				$callback = array($this, $callback);
			}
			elseif (method_exists('KohanaValidationHelpers', $callback))
			{
				// The callback exists in KohanaValidationHelpers::
				$callback = array('KohanaValidationHelpers', $callback);
			}
		}

		if ( ! is_callable($callback, FALSE))
		{
			if (is_array($callback))
			{
				if (is_object($callback[0]))
				{
					// Object instance syntax
					$name = get_class($callback[0]).'->'.$callback[1];
				}
				else
				{
					// Static class syntax
					$name = $callback[0].'::'.$callback[1];
				}
			}
			else
			{
				// Function syntax
				$name = $callback;
			}
			
			throw new Exception("Callback {$name} used for Validation is not callable");
		}

		return $callback;
	}

	/**
	 * Add a pre-filter to one or more inputs. Pre-filters are applied before
	 * rules or callbacks are executed.
	 *
	 * @chainable
	 * @param   callback  filter
	 * @param   string    fields to apply filter to, use TRUE for all fields
	 * @return  object
	 */
	public function pre_filter($filter, $field = TRUE)
	{
		if ($field === TRUE OR $field === '*')
		{
			// Use wildcard
			$fields = array('*');
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		// Convert to a proper callback
		$filter = $this->callback($filter);

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->pre_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add a post-filter to one or more inputs. Post-filters are applied after
	 * rules and callbacks have been executed.
	 *
	 * @chainable
	 * @param   callback  filter
	 * @param   string    fields to apply filter to, use TRUE for all fields
	 * @return  object
	 */
	public function post_filter($filter, $field = TRUE)
	{
		if ($field === TRUE)
		{
			// Use wildcard
			$fields = array('*');
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		// Convert to a proper callback
		$filter = $this->callback($filter);

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->post_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add rules to a field. Validation rules may only return TRUE or FALSE and
	 * can not manipulate the value of a field.
	 *
	 * @chainable
	 * @param   string    field name
	 * @param   callback  rules (one or more arguments)
	 * @return  object
	 */
	public function add_rules($field, $rules)
	{
		// Get the rules
		$rules = func_get_args();
		$rules = array_slice($rules, 1);

		if ($field === TRUE)
		{
			// Use wildcard
			$field = '*';
		}

		foreach ($rules as $rule)
		{
			// Arguments for rule
			$args = NULL;

			if (is_string($rule))
			{
				// Split the rule into the function and args
				list($rule, $args) = $this->split_rule_string($rule);
			}

			if ($rule === 'is_array')
			{
				// This field is expected to be an array
				$this->array_fields[$field] = $field;
			}

			// Convert to a proper callback
			$rule = $this->callback($rule);

			// Add the rule, with args, to the field
			$this->rules[$field][] = array($rule, $args);
		}

		return $this;
	}
	
	/**
	 * Add one rule for one field, along with its error message
	 */
	public function add_rule($field, $rule, $error_message = null) {
		$this->add_rules($field, $rule);
		if (!is_null($error_message)) {
			if (is_string($rule)) {
				list($rule, $args) = $this->split_rule_string($rule); //extract just the rule name (ignore "args" -- e.g. if "length[0,10]" is the rule, we just want "length")
			} else {
				$rule = $rule[1]; //$rule is an array (object and method name), so just grab the method name
			}
			$this->add_message($field, $rule, $error_message);
		}
	}

	/**
	 * Internal helper function for add_rule/add_rules methods
	 */
	private function split_rule_string($rule) {
		$args = NULL;

		if (preg_match('/^([^\[]++)\[(.+)\]$/', $rule, $matches))
		{
			// Split the rule into the function and args
			$rule = $matches[1];
			$args = preg_split('/(?<!\\\\),\s*/', $matches[2]);

			// Replace escaped comma with comma
			$args = str_replace('\,', ',', $args);
		}

		return array($rule, $args);
	}

	/**
	 * Add callbacks to a field. Callbacks must accept the Validation object
	 * and the input name. Callback returns are not processed.
	 *
	 * @chainable
	 * @param   string     field name
	 * @param   callbacks  callbacks (unlimited number)
	 * @return  object
	 */
	public function add_callbacks($field, $callbacks)
	{
		// Get all callbacks as an array
		$callbacks = func_get_args();
		$callbacks = array_slice($callbacks, 1);

		if ($field === TRUE)
		{
			// Use wildcard
			$field = '*';
		}

		foreach ($callbacks as $callback)
		{
			// Convert to a proper callback
			$callback = $this->callback($callback);

			// Add the callback to specified field
			$this->callbacks[$field][] = $callback;
		}

		return $this;
	}

	/**
	 * Add one callback for one field, along with its error message
	 */
	public function add_callback($field, $callback, $error_message = null) {
		$this->add_callbacks($field, $callback);
		if (!is_null($error_message)) {
			if (is_array($callback)) {
				$rule = $callback[1];
			} else if (is_string($callback)) {
				$rule = $callback;
			} else {
				//uhh... not sure how to handle this (just don't pass something other than an array or string!)
				return;
			}
			$this->add_message($field, $rule, $error_message);
		}
	}

	/**
	 * Validate by processing pre-filters, rules, callbacks, and post-filters.
	 * All fields that have filters, rules, or callbacks will be initialized if
	 * they are undefined. Validation will only be run if there is data already
	 * in the array.
	 *
	 * @param   object  KohanaValidation object, used only for recursion
	 * @param   object  name of field for errors
	 * @return  bool
	 */
	public function validate($object = NULL, $field_name = NULL)
	{
		if ($object === NULL)
		{
			// Use the current object
			$object = $this;
		}

		// Get all field names
		$fields = $this->field_names();

		// Copy the array from the object, to optimize multiple sets
		$array = $this->getArrayCopy();

		foreach ($fields as $field)
		{
			if ($field === '*')
			{
				// Ignore wildcard
				continue;
			}

			if ( ! isset($array[$field]))
			{
				if (isset($this->array_fields[$field]))
				{
					// This field must be an array
					$array[$field] = array();
				}
				else
				{
					$array[$field] = NULL;
				}
			}
		}

		// Swap the array back into the object
		$this->exchangeArray($array);

		// Get all defined field names
		$fields = array_keys($array);

		foreach ($this->pre_filters as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						$this[$f] = is_array($this[$f]) ? array_map($callback, $this[$f]) : call_user_func($callback, $this[$f]);
					}
				}
				else
				{
					$this[$field] = is_array($this[$field]) ? array_map($callback, $this[$field]) : call_user_func($callback, $this[$field]);
				}
			}
		}

		if ($this->submitted === FALSE)
			return FALSE;

		foreach ($this->rules as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				// Separate the callback and arguments
				list ($callback, $args) = $callback;

				// Function or method name of the rule
				$rule = is_array($callback) ? $callback[1] : $callback;

				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						// Note that continue, instead of break, is used when
						// applying rules using a wildcard, so that all fields
						// will be validated.

						if (isset($this->errors[$f]))
						{
							// Prevent other rules from being evaluated if an error has occurred
							continue;
						}

						if (empty($this[$f]) AND ! in_array($rule, $this->empty_rules))
						{
							// This rule does not need to be processed on empty fields
							continue;
						}

						if ($args === NULL)
						{
							if ( ! call_user_func($callback, $this[$f]))
							{
								$this->errors[$f] = $rule;

								// Stop validating this field when an error is found
								continue;
							}
						}
						else
						{
							if ( ! call_user_func($callback, $this[$f], $args))
							{
								$this->errors[$f] = $rule;

								// Stop validating this field when an error is found
								continue;
							}
						}
					}
				}
				else
				{
					if (isset($this->errors[$field]))
					{
						// Prevent other rules from being evaluated if an error has occurred
						break;
					}

					if ( ! in_array($rule, $this->empty_rules) AND ! $this->required($this[$field]))
					{
						// This rule does not need to be processed on empty fields
						continue;
					}

					if ($args === NULL)
					{
						if ( ! call_user_func($callback, $this[$field]))
						{
							$this->errors[$field] = $rule;

							// Stop validating this field when an error is found
							break;
						}
					}
					else
					{
						if ( ! call_user_func($callback, $this[$field], $args))
						{
							$this->errors[$field] = $rule;

							// Stop validating this field when an error is found
							break;
						}
					}
				}
			}
		}

		foreach ($this->callbacks as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						// Note that continue, instead of break, is used when
						// applying rules using a wildcard, so that all fields
						// will be validated.

						if (isset($this->errors[$f]))
						{
							// Stop validating this field when an error is found
							continue;
						}

						call_user_func($callback, $this, $f);
					}
				}
				else
				{
					if (isset($this->errors[$field]))
					{
						// Stop validating this field when an error is found
						break;
					}

					call_user_func($callback, $this, $field);
				}
			}
		}

		foreach ($this->post_filters as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if ($field === '*')
				{
					foreach ($fields as $f)
					{
						$this[$f] = is_array($this[$f]) ? array_map($callback, $this[$f]) : call_user_func($callback, $this[$f]);
					}
				}
				else
				{
					$this[$field] = is_array($this[$field]) ? array_map($callback, $this[$field]) : call_user_func($callback, $this[$field]);
				}
			}
		}

		// Return TRUE if there are no errors
		return $this->errors === array();
	}

	/**
	 * Add an error to an input.
	 *
	 * @chainable
	 * @param   string  input name
	 * @param   string  unique error name
	 * @return  object
	 */
	public function add_error($field, $name)
	{
		$this->errors[$field] = $name;

		return $this;
	}

	/**
	 * Return the errors as an array or as a C5 "validation/error" object.
	 *
	 * @return  array
	 */
	public function errors($as_c5_error_object = false)
	{
		$errors = array();
		$c5error = Loader::helper('validation/error');
		foreach ($this->errors as $input => $error_key)
		{
			if (isset($this->messages[$input][$error_key]))
			{
				$message = $this->messages[$input][$error_key]; 
			}
			else
			{
				$message = $input . ': ' . $error_key;
			}
			$errors[$input] = $message;
			$c5error->add($message);
		}
		
		return $as_c5_error_object ? $c5error : $errors;
	}
	
	/**
	 * Initialize all error messages at once (will overwrite any existing ones).
	 *
	 *  Must be in the form of:
	 *    $messages = array(
	 *        'fieldname' => array(
	 *            'rulename' => 'Error message.',
	 *            'anotherrulename' => 'Another error message.',
	 *        ),
	 *        'anotherfieldname' => array(
	 *            'somerulename' => 'Some error message.',
	 *            'yetanotherrulename' => 'Yet Another error message.',
	 *        ),
	 *    );
	 */
	public function set_messages(array $messages) {
		$this->messages = $messages;
	}
	
	/**
	 * Add one error message (for one rule of one input)
	 */
	public function add_message($field, $rule, $message) {
		$this->messages[$field][$rule] = $message;
	}
	
	/**
	 * Rule: required. Generates an error if the field has an empty value.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function required($str)
	{
		if (is_object($str) AND $str instanceof ArrayObject)
		{
			// Get the array from the ArrayObject
			$str = $str->getArrayCopy();
		}

		if (is_array($str))
		{
			return ! empty($str);
		}
		else
		{
			return ! ($str === '' OR $str === NULL OR $str === FALSE);
		}
	}

	/**
	 * Rule: matches. Generates an error if the field does not match one or more
	 * other fields.
	 *
	 * @param   mixed   input value
	 * @param   array   input names to match against
	 * @return  bool
	 */
	public function matches($str, array $inputs)
	{
		foreach ($inputs as $key)
		{
			if ($str !== (isset($this[$key]) ? $this[$key] : NULL))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * @param   mixed   input value
	 * @param   array   minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		if (function_exists('mb_strlen')) {
			$size = mb_strlen($str, APP_CHARSET);
		} else {
			$size = strlen($str);
		}
		
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}

	/**
	 * Rule: depends_on. Generates an error if the field does not depend on one
	 * or more other fields.
	 *
	 * @param   mixed   field name
	 * @param   array   field names to check dependency
	 * @return  bool
	 */
	public function depends_on($field, array $fields)
	{
		foreach ($fields as $depends_on)
		{
			if ( ! isset($this[$depends_on]) OR $this[$depends_on] == NULL)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: chars. Generates an error if the field contains characters outside of the list.
	 *
	 * @param   string  field value
	 * @param   array   allowed characters
	 * @return  bool
	 */
	public function chars($value, array $chars)
	{
		return ! preg_match('![^'.implode('', $chars).']!u', $value);
	}

} // End KohanaValidation


///////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * Kohana-2.3.4's Validation helper class.
 * (Also includes some methods from Kohana's "upload" helper.)
 *
 * $Id: valid.php 4367 2009-05-27 21:23:57Z samsoir $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class KohanaValidationHelpers {

	/**
	 * Validate email, commonly used characters only
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email($email)
	{
		return (bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', (string) $email);
	}

	/**
	 * Validate the domain of an email address by checking if the domain has a
	 * valid MX record.
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email_domain($email)
	{
		// If we can't prove the domain is invalid, consider it valid
		// Note: checkdnsrr() is not implemented on Windows platforms
		if ( ! function_exists('checkdnsrr'))
			return TRUE;

		// Check if the email domain has a valid MX record
		return (bool) checkdnsrr(preg_replace('/^[^@]+@/', '', $email), 'MX');
	}

	/**
	 * Validate email, RFC compliant version
	 * Note: This function is LESS strict than valid_email. Choose carefully.
	 *
	 * @see  Originally by Cal Henderson, modified to fit Kohana syntax standards:
	 * @see  http://www.iamcal.com/publish/articles/php/parsing_email/
	 * @see  http://www.w3.org/Protocols/rfc822/
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email_rfc($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$pair  = '\\x5c[\\x00-\\x7f]';

		$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
		$quoted_string  = "\\x22($qtext|$pair)*\\x22";
		$sub_domain     = "($atom|$domain_literal)";
		$word           = "($atom|$quoted_string)";
		$domain         = "$sub_domain(\\x2e$sub_domain)*";
		$local_part     = "$word(\\x2e$word)*";
		$addr_spec      = "$local_part\\x40$domain";

		return (bool) preg_match('/^'.$addr_spec.'$/D', (string) $email);
	}

	/**
	 * Validate URL
	 *
	 * @param   string   URL
	 * @return  boolean
	 */
	public static function url($url)
	{
		return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
	}

	/**
	 * Validate IP
	 *
	 * @param   string   IP address
	 * @param   boolean  allow IPv6 addresses
	 * @param   boolean  allow private IP networks
	 * @return  boolean
	 */
	public static function ip($ip, $ipv6 = FALSE, $allow_private = TRUE)
	{
		// By default do not allow private and reserved range IPs
		$flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
		if ($allow_private === TRUE)
			$flags =  FILTER_FLAG_NO_RES_RANGE;

		if ($ipv6 === TRUE)
			return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags | FILTER_FLAG_IPV4);
	}

	/**
	 * Validates a credit card number using the Luhn (mod10) formula.
	 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
	 *
	 * @param   integer       credit card number
	 * @param   string|array  card type, or an array of card types
	 * @return  boolean
	 */
	public static function credit_card($number, $type = NULL)
	{
		// Remove all non-digit characters from the number
		if (($number = preg_replace('/\D+/', '', $number)) === '')
			return FALSE;

		if ($type == NULL)
		{
			// Use the default type
			$type = 'default';
		}
		elseif (is_array($type))
		{
			foreach ($type as $t)
			{
				// Test each type for validity
				if (KohanaValidationHelpers::credit_card($number, $t))
					return TRUE;
			}

			return FALSE;
		}

		
		/**
		 * Credit card validation configuration.
		 * 
		 * Options for each credit card:
		 *  length - All the allowed card number lengths, in a comma separated string
		 *  prefix - The digits the card needs to start with, in regex format
		 *  luhn   - Enable or disable card number validation by the Luhn algorithm
		 */
		$cards = array(
			'default' => array(
				'length' => '13,14,15,16,17,18,19',
				'prefix' => '',
				'luhn'   => TRUE
			),
			'american express' => array(
				'length' => '15',
				'prefix' => '3[47]',
				'luhn'   => TRUE
			),
			'diners club' => array(
				'length' => '14,16',
				'prefix' => '36|55|30[0-5]',
				'luhn'   => TRUE
			),
			'discover' => array(
				'length' => '16',
				'prefix' => '6(?:5|011)',
				'luhn'   => TRUE,
			),
			'jcb' => array(
				'length' => '15,16',
				'prefix' => '3|1800|2131',
				'luhn'   => TRUE
			),
			'maestro' => array(
				'length' => '16,18',
				'prefix' => '50(?:20|38)|6(?:304|759)',
				'luhn'   => TRUE
			),
			'mastercard' => array(
				'length' => '16',
				'prefix' => '5[1-5]',
				'luhn'   => TRUE
			),
			'visa' => array(
				'length' => '13,16',
				'prefix' => '4',
				'luhn'   => TRUE
			),
		);
		
		// Check card type
		$type = strtolower($type);

		if ( ! isset($cards[$type]))
			return FALSE;

		// Check card number length
		$length = strlen($number);

		// Validate the card length by the card type
		if ( ! in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
			return FALSE;

		// Check card number prefix
		if ( ! preg_match('/^'.$cards[$type]['prefix'].'/', $number))
			return FALSE;

		// No Luhn check required
		if ($cards[$type]['luhn'] == FALSE)
			return TRUE;

		// Checksum of the card number
		$checksum = 0;

		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit, starting from the right
			$checksum += $number[$i];
		}

		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit doubled, starting from the right
			$double = $number[$i] * 2;

			// Subtract 9 from the double where value is greater than 10
			$checksum += ($double >= 10) ? $double - 9 : $double;
		}

		// If the checksum is a multiple of 10, the number is valid
		return ($checksum % 10 === 0);
	}

	/**
	 * Checks if a phone number is valid.
	 *
	 * @param   string   phone number to check
	 * @return  boolean
	 */
	public static function phone($number, $lengths = NULL)
	{
		if ( ! is_array($lengths))
		{
			$lengths = array(7,10,11);
		}

		// Remove all non-digit characters from the number
		$number = preg_replace('/\D+/', '', $number);

		// Check if the number is within range
		return in_array(strlen($number), $lengths);
	}

	/**
	 * Tests if a string is a valid date string.
	 * 
	 * @param   string   date to check
	 * @return  boolean
	 */
	public static function date($str)
	{
		return (strtotime($str) !== FALSE);
	}

	/**
	 * Checks whether a string consists of alphabetical characters only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^\pL++$/uD', (string) $str)
			: ctype_alpha((string) $str);
	}

	/**
	 * Checks whether a string consists of alphabetical characters and numbers only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_numeric($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^[\pL\pN]++$/uD', (string) $str)
			: ctype_alnum((string) $str);
	}

	/**
	 * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_dash($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^[-\pL\pN_]++$/uD', (string) $str)
			: (bool) preg_match('/^[-a-z0-9_]++$/iD', (string) $str);
	}

	/**
	 * Checks whether a string consists of digits only (no dots or dashes).
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function digit($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^\pN++$/uD', (string) $str)
			: ctype_digit((string) $str);
	}

	/**
	 * Checks whether a string is a valid number (negative and decimal numbers allowed).
	 *
	 * @see Uses locale conversion to allow decimal point to be locale specific.
	 * @see http://www.php.net/manual/en/function.localeconv.php
	 * 
	 * @param   string   input string
	 * @return  boolean
	 */
	public static function numeric($str)
	{
		// Use localeconv to set the decimal_point value: Usually a comma or period.
		$locale = localeconv();
		return (bool) preg_match('/^-?[0-9'.$locale['decimal_point'].']++$/D', (string) $str);
	}

	/**
	 * Checks whether a string is a valid text. Letters, numbers, whitespace,
	 * dashes, periods, and underscores are allowed.
	 *
	 * @param   string   text to check
	 * @return  boolean
	 */
	public static function standard_text($str)
	{
		// pL matches letters
		// pN matches numbers
		// pZ matches whitespace
		// pPc matches underscores
		// pPd matches dashes
		// pPo matches normal puncuation
		return (bool) preg_match('/^[\pL\pN\pZ\p{Pc}\p{Pd}\p{Po}]++$/uD', (string) $str);
	}

	/**
	 * Checks if a string is a proper decimal format. The format array can be
	 * used to specify a decimal length, or a number and decimal length, eg:
	 * array(2) would force the number to have 2 decimal places, array(4,2)
	 * would force the number to have 4 digits and 2 decimal places.
	 *
	 * @param   string   input string
	 * @param   array    decimal format: y or x,y
	 * @return  boolean
	 */
	public static function decimal($str, $format = NULL)
	{
		// Create the pattern
		$pattern = '/^[0-9]%s\.[0-9]%s$/';

		if ( ! empty($format))
		{
			if (count($format) > 1)
			{
				// Use the format for number and decimal length
				$pattern = sprintf($pattern, '{'.$format[0].'}', '{'.$format[1].'}');
			}
			elseif (count($format) > 0)
			{
				// Use the format as decimal length
				$pattern = sprintf($pattern, '+', '{'.$format[0].'}');
			}
		}
		else
		{
			// No format
			$pattern = sprintf($pattern, '+', '+');
		}

		return (bool) preg_match($pattern, (string) $str);
	}
	
	/**
	 * Checks if a string is a number within a given range (inclusive).
	 *
	 * To only check if the given number is greater than something or
	 * less than something, pass null for max or min (respectively).
	 * For example, array(0,10) check that the number is between 0 and 10,
	 * while array(null, 10) checks that the number is 10 or less
	 * (and array(0, null) checks that the number is 0 or greater).
	 *
	 * If a non-numeric string is passed in for the number, we return false.
	 *
	 * @param   string   input string
	 * @param   array    minimum, maximum
	 * @return  boolean
	 */
	public static function inrange($number, array $range) //NOTE: Can't name this function "range" because that's already a built-in php function.
	{
		$is_valid = false;
		
		if (is_numeric($number) && count($range) == 2)
		{
			$floor = is_numeric($range[0]) ? $range[0] : 0;
			$ceil = is_numeric($range[1]) ? $range[1] : 0;

			if (is_null($range[0]) && is_null($range[1])) {
				$is_valid = true;
			} else if (is_null($range[0])) {
				$is_valid = ($number <= $ceil);
			} else if (is_null($range[1])) {
				$is_valid = ($number >= $floor);
			} else {
				$is_valid = ($number >= $floor && $number <= $ceil);
			}
		}
		
		return $is_valid;
	}

	/**
	 * Checks if a string is a number greater than or equal to a minimum value.
	 */
	public static function atleast($number, $minval) //NOTE: Can't name this function "min" because that's already a built-in php function.
	{
		$minval = is_array($minval) ? $minval[0] : $minval;
		return KohanaValidationHelpers::inrange($number, array($minval, null));
	}

	/**
	 * Checks if a string is a number less than or equal to a maximum value.
	 */
	public static function atmost($number, $maxval) //NOTE: Can't name this function "max" because that's already a built-in php function.
	{
		$maxval = is_array($maxval) ? $maxval[0] : $maxval;
		return KohanaValidationHelpers::inrange($number, array(null, $maxval));
	}


	/* File Upload Validation Rules */

	/**
	 * Tests if input data is valid file type, even if no upload is present.
	 * (You should basically always call this on your file fields, even ones
	 * that aren't required -- this ensures there's no funny stuff going on).
	 *
	 * @param   array  $_FILES item
	 * @return  bool
	 */
	public static function upload_valid($file)
	{
		return (is_array($file)
			AND isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size']));
	}

	/**
	 * Tests if input data has valid upload data.
	 *
	 * @param   array    $_FILES item
	 * @return  bool
	 */
	public static function upload_required(array $file)
	{
		return (isset($file['tmp_name'])
			AND isset($file['error'])
			AND is_uploaded_file($file['tmp_name'])
			AND (int) $file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by extension.
	 *
	 * @param   array    $_FILES item
	 * @param   array    allowed file extensions
	 * @return  bool
	 */
	public static function upload_type(array $file, array $allowed_types)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Get the default extension of the file
		$extension = strtolower(substr(strrchr($file['name'], '.'), 1));

		// Make sure there is an extension, and that the extension is allowed
		return ( ! empty($extension) AND in_array($extension, $allowed_types) );
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size.
	 * File sizes are defined as: SB, where S is the size (1, 15, 300, etc) and
	 * B is the byte modifier: (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
	 * Eg: to limit the size to 1MB or less, you would use "1M".
	 *
	 * @param   array    $_FILES item
	 * @param   array    maximum file size
	 * @return  bool
	 */
	public static function upload_size(array $file, array $size)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Only one size is allowed
		$size = strtoupper($size[0]);

		if ( ! preg_match('/[0-9]++[BKMG]/', $size))
			return FALSE;

		// Make the size into a power of 1024
		switch (substr($size, -1))
		{
			case 'G': $size = intval($size) * pow(1024, 3); break;
			case 'M': $size = intval($size) * pow(1024, 2); break;
			case 'K': $size = intval($size) * pow(1024, 1); break;
			default:  $size = intval($size);                break;
		}

		// Test that the file is under or equal to the max size
		return ($file['size'] <= $size);
	}

} // End KohanaValidationHelpers
