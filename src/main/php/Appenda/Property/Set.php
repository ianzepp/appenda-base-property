<?php

/**
 * The MIT License
 * 
 * Copyright (c) 2009 Ian Zepp
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author Ian Zepp
 * @package 
 *
 * A property set is a collection of variables (or attributes), handled in a strict-type fashion.
 *
 * <b>MAGIC METHODS</b>
 *
 * Assuming the following attribute set and registered attributes:
 *
 * <code>
 * $oSet = new Appenda_Property_Set();
 * $oSet->register( 'Sample', 'String' );
 * $oSet->register( 'Mapping', 'Array' );
 * </code>
 *
 * The following accessors and mutators are magically available:
 *
 * <code>
 * $oSet->getSample();
 * $oSet->setSample( 'Some Value' );
 * </code>
 *
 * The following basic array methods are available as well for array types:
 *
 * <code>
 * $oSet->getMapping( 'Key' );
 * $oSet->setMapping( 'Key', 'Value' );
 * $oSet->appendToMapping( 'Value' );
 * $oSet->appendToMapping( 'Key', 'Value' );
 * $oSet->prependToMapping( 'Value' );
 * $oSet->prependToMapping( 'Key', 'Value' );
 * $oSet->countMapping();
 * </code>
 *
 * Inspection of internal properties are available:
 *
 * <code>
 * $oSet->inspectSample(); // Returns the internal Appenda_Property object.
 * </code>
 *
 * The following value tests are available, and return true/false. While not shown,
 * all registered property types have access to these methods.
 *
 * <code>
 * $oSet->isSampleNull();
 * $oSet->isSampleNotNull();
 * $oSet->isSampleEmpty();
 * $oSet->isSampleNotEmpty();
 * $oSet->isSampleTrue();  // Strict type checking.
 * $oSet->isSampleFalse(); // String type checking.
 * </code>
 *
 * The following assertations are available, and throw an exception if ASSERT_BAIL is enabled,
 * print a warning and return an Exception object if ASSERT_WARNING is enabled, or simply do
 * nothing and return null is ASSERT_ACTIVE is off. While not shown, all registered property
 * types have access to these methods.
 *
 * <code>
 * $oSet->assertSampleNull();
 * $oSet->assertSampleNotNull();
 * $oSet->assertSampleEmpty();
 * $oSet->assertSampleNotEmpty();
 * $oSet->assertSampleTrue();  // Strict type checking.
 * $oSet->assertSampleFalse(); // Strict type checking.
 * </code>
 *
 * <b>PROPERTY MODES</b>
 *
 * Modes are used to control the behavior of individual attributes. The modes are:
 *
 * - {@link MODE_ACCESSOR}.
 * - {@link MODE_ALIAS}.
 * - {@link MODE_CALLBACK}.
 * - {@link MODE_CALLBACK_CACHED}.
 * - {@link MODE_COMPRESSED}.
 * - {@link MODE_INTERNAL}.
 * - {@link MODE_LOCKED}.
 * - {@link MODE_MUTATOR}.
 * - {@link MODE_PERMISSIVE}.
 * - {@link MODE_SHARED}.
 *
 * Modes are bitmapped values, so you can do a bitwise | operator to combine several modes
 * in a single method call. To apply a mode to a property:
 *
 * <code>
 * $oSet = new Appenda_Property_Set();
 * $oSet->register( 'Sample', 'string' );
 * $oSet->registerMode( Appenda_Property_Set::MODE_COMPRESSED );
 * </code>
 *
 * <b>TEMPLATING</b>
 *
 * This class has two main methods ({@link registerInstance()}, {@link newInstance()}) that allow
 * you to save a cloned instance of a validated Appenda_Property_Set, so you can recreate the instance in
 * the future without incuring the validation cost again.
 *
 * For example, a complex object might include 10-15 attributes, each of which have 1-3 modes and perhaps
 * 1-3 rules. Each time any of the registerXX() methods are called, an extensive set of name, type, and
 * mode validations are performed. This is done to ensure that the internal state of the attribute set
 * remains intact at all times.
 *
 * By calling the {@link registerInstance()} method after the first instance of a class, a validated
 * copy is saved, and subsequent instances can be created (fully initialized and validated) by using
 * the {@link newInstance()} method.
 *
 * This is a substantial performance savings, even for simple objects:
 * 1. Using unit tests to benchmark, a simple attribute set with three attributes and no rules or modes
 *    shows an 80% decrease in creation time.
 * 2. A complex attribute set with approximately 10 attributes, 5 modes, and 10 rules show an improvement
 *    of two orders of magnitude.
 *
 * Registering and cloning template is quite simple. Please see the code example in the INHERITANCE
 * section below.
 *
 * <b>INHERITANCE</b>
 *
 * The easiest way to use a {@link Appenda_Property_Set} is to inherit from it. This gives you full access
 * to the magic get, set, is, and assertIs methods, as well as providing a clean way to initialize
 * the attributes, rules, and modes. For example:
 *
 * <code>
 * class WebsiteRequest extends Appenda_Property_Set
 * {
 *     protected function __construct()
 *     {
 *         $this->register( 'Host', 'string' );
 *         $this->register( 'RequestUrl', 'string' );
 *         $this->registerTemplate( __CLASS__ );
 *     }
 *
 *     static public function newInstance( array $aRequestParams )
 *     {
 *         // Create or clone the template instance
 *         $oInstance = parent::newInstance( __CLASS__ );
 *
 *         // Update the internal attributes
 *         $oInstance->setHost( $aRequestParams['REQUEST_HOST'] );
 *         $oInstance->setRequestUrl( $aRequestParams['REQUEST_URI') );
 *
 *         // Development-time assertations
 *         $oInstance->assertHostNotEmpty();
 *         $oInstance->assertRequestUrlNotEmpty();
 *
 *         // Return the validated instance
 *         return $oInstance;
 *     }
 * }
 * </code>
 *
 * <b>FILTERS</b>
 *
 * Filters are a special set of conditions that are tested prior to the updating of a property's
 * value. All filters must return true for the value to be updated. You can assign any number of
 * filters, in any combination of the following, with multiple filters of the same type allowed:
 *
 * - {@link Appenda_Property_Filter::CALLBACK}.
 * - {@link Appenda_Property_Filter::INVERSE_REGEX}.
 * - {@link Appenda_Property_Filter::MAXIMUM}.
 * - {@link Appenda_Property_Filter::MINIMUM}.
 * - {@link Appenda_Property_Filter::REGEX}.
 * - {@link Appenda_Property_Filter::UNSIGNED}.
 *
 * Most notably, callback filters can be assigned, so you can pass the data through an external
 * function someplace for validation.
 *
 * Filters can be set directly using the {@link Appenda_Property_Set} class:
 *
 * <code>
 * $oSet = new Appenda_Property_Set();
 * $oSet->register( 'RangedInteger', 'Integer' );
 * $oSet->appendFilter( 'RangedInteger', Appenda_Property_Filter::MINIMUM, -10 );
 * $oSet->appendFilter( 'RangedInteger', Appenda_Property_Filter::MAXIMUM, +10 );
 *
 * $oSet->register( 'RangedString', 'String' );
 * $oSet->appendFilter( 'RangedString', Appenda_Property_Filter::MINIMUM, 4 );
 * $oSet->appendFilter( 'RangedString', Appenda_Property_Filter::MAXIMUM, 8 );
 *
 * // A more complicated example:
 * //
 * // Note the multiple regex rules. In this case, BOTH regexes have to match, which
 * // effectively means that the password has to have both at least one number and one
 * // letter, and the password as a whole also has to have a minimum length of 8
 * // characters and a maximum of 100.
 *
 * $oSet->register( 'PasswordString', 'String' );
 * $oSet->appendFilter( 'PasswordString', Appenda_Property_Filter::MINIMUM, 8 );
 * $oSet->appendFilter( 'PasswordString', Appenda_Property_Filter::MAXIMUM, 100 );
 * $oSet->appendFilter( 'PasswordString', Appenda_Property_Filter::REGEX, '/[a-zA-Z]+/' );
 * $oSet->appendFilter( 'PasswordString', Appenda_Property_Filter::REGEX, '/[0-9]+/' );
 * </code>
 *
 * Filters can also be set and accessed on the individual {@link Appenda_Property} itself, using
 * the inspect<Property>() and then accessing the 'Filters' field:
 *
 * <code>
 * $oProperty = $oSet->register( 'PasswordString', 'String' );
 * $oProperty->appendToFilters( ... );
 * $oProperty->getFilters();
 * $oProperty->setFilters( ... );
 * </code>
 *
 * <b>TRIGGERS</b>
 *
 * Triggers are similar to filters, but are exclusively callbacks that are executed in
 * order after the field is updated. No return value or success/failure is checked.
 *
 * Triggers are added in a similar fashion to filters:
 *
 * <code>
 * $oSet = new Appenda_Property_Set();
 * $oSet->register( 'OrderStatus', 'Integer' );
 * $oSet->appendTrigger( 'OrderStatus', array( 'AuditHandler', 'logOrderChange' ) );
 * $oSet->appendTrigger( 'OrderStatus', array( 'OrderHandler', 'updateStatus' ) );
 * </code>
 *
 */

require_once "Appenda/Property.php";
require_once "Appenda/Property/Atom.php";
require_once "Appenda/Property/Exception.php";
require_once "Appenda/Property/Filter.php";

class Appenda_Property_Set extends Appenda_Property_Atom {
	/**
	 * @param string $sClassName
	 * @return Appenda_Property_Set
	 */
	static public function newInstance ($sClassName = null) {
		return parent::newInstance (empty ($sClassName) ? __CLASS__ : $sClassName);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sType
	 * @param mixed $mDefaultValue
	 * @return Appenda_Property
	 */
	public function register ($sName, $sType, $mDefaultValue = null) {
		assert (is_string ($sName));
		assert (is_string ($sType));
		
		$oInstance = Appenda_Property::newInstance ();
		$oInstance->setName ($sName);
		$oInstance->setType ($sType);
		$oInstance->setDefaultValue ($mDefaultValue);
		parent::register ($sName, 'Appenda_Property', $oInstance);
		return $oInstance;
	}
	
	/**
	 * Register an alias to an existing attribute name.
	 *
	 * @param string $sAliasedName
	 * @param string $sName
	 * @return boolean
	 */
	public function registerAlias ($sAliasedName, $sName) {
		assert (is_string ($sAliasedName));
		assert (is_string ($sName));
		
		$sInspectMethod = 'inspect' . $sName;
		$oInstance = $this->register ($sAliasedName, $this->$sInspectMethod ()->getType ());
		$oInstance->setAlias ($sName); // this must happen first.
		$oInstance->setAliased (true);
		return $oInstance;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sType
	 * @param array $aCallback
	 * @param boolean $bCached
	 * @return Appenda_Property
	 */
	public function registerCallback ($sName, $sType, array $aCallback, $bCached = false) {
		assert (is_string ($sName));
		assert (is_string ($sType));
		assert (is_boolean ($bCached));
		
		$oInstance = $this->register ($sName, $sType);
		$oInstance->setCallback ($aCallback);
		$oInstance->setCached ($bCached);
		return $oInstance;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sFilterType
	 * @param mixed $mFilterData
	 */
	public function registerFilter ($sName, $sFilterType, $mFilterData) {
		assert (is_string ($sName));
		assert (is_string ($sFilterType));
		
		$oFilter = Appenda_Property_Filter::newInstance ($sFilterType, $mFilterData);
		$sInstanceMethod = 'inspect' . $sName;
		$oInstance = $this->$sInstanceMethod ();
		$oInstance->setFilterable (true);
		$oInstance->appendToFilters ($oFilter);
		return $oInstance;
	}
	
	/**
	 * Convenience method
	 *
	 * @param string $sName
	 * @param integer $mMinimum
	 * @param integer $mMaximum
	 */
	public function registerRangeFilter ($sName, $iMinimum = PHP_INT_MIN, $iMaximum = PHP_INT_MAX) {
		$this->registerFilter ($sName, Appenda_Property_Filter::MINIMUM, $iMinimum);
		$this->registerFilter ($sName, Appenda_Property_Filter::MAXIMUM, $iMaximum);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param array $aCallback
	 */
	public function registerTrigger ($sName, array $aCallback) {
		assert (is_string ($sName));
		
		$sInstanceMethod = 'inspect' . $sName;
		$oInstance = $this->$sInstanceMethod ();
		$oInstance->setTriggerable (true);
		$oInstance->appendToTriggers ($aCallback);
		return $oInstance;
	}
	
	/**
	 * @param string $sMethod
	 * @param array $aArguments
	 * @return mixed
	 */
	public function __call ($sMethod, array $aArguments) {
		assert (is_string ($sMethod));
		
		$aMatches = array ();
		$aPropertyAliases = array ();
		
		// Overriden magic methods at this class level
		if (preg_match ('/^(get|inspect|set)(.+)$/', $sMethod, $aMatches)) {
			list ($sMethod, $sCommand, $sProperty) = $aMatches;
		} else {
			return parent::__call ($sMethod, $aArguments);
		}
		
		// Fetch the original property
		$oProperty = $this->__callget ($sProperty);
		
		// Loop through the aliases
		while ($oProperty->isAliasedTrue ()) {
			// Loop detection
			if (count ($aPropertyAliases) > 1000) {
				throw new Appenda_Property_Exception ('Exceeded maximum allowed alias depth of 1000 objects.');
			} elseif (in_array ($oProperty, $aPropertyAliases, true)) {
				throw new Appenda_Property_Exception ('Alias loop detected!');
			} else {
				$aPropertyAliases [] = $oProperty;
				$oProperty = $this->__callget ($oProperty->getAlias ());
			}
		}
		
		// Switch to the proper magic command
		switch ($sCommand) {
			case 'get' :
				return $oProperty->getValue (array_shift ($aArguments));
			
			case 'set' :
				return $oProperty->setValue (array_shift ($aArguments), array_shift ($aArguments));
			
			case 'inspect' :
				return $oProperty;
		
		}
		
		$sMessage = 'Should never get here';
		$sMessage .= ', method=' . $sMethod;
		throw new Appenda_Property_Exception ($sMessage);
	}
	
	/**
	 * @param string $sName
	 * @param string $sComparison
	 * @return boolean
	 * @see Appenda_Property_Atom::__is()
	 */
	protected function __is ($sName, $sComparison) {
		assert (is_string ($sName));
		assert (is_string ($sComparison));
		
		$oProperty = $this->__callget ($sName);
		$sPropertyMethod = 'isValue' . $sComparison;
		return $oProperty->$sPropertyMethod ();
	}

}

