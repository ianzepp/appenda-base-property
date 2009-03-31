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
 */

require_once "Appenda/Property/Exception.php";

abstract class Appenda_Property_Atom {
	/**
	 * Add a data element to the end of an array
	 *
	 * @param string $sName
	 * @param mixed $mData
	 * @return Appenda_Property_Atom
	 */
	protected function __appendTo ($sName, $mData) {
		assert (isset ($this->__aInternals));
		assert (isset ($this->__aInternals [$sName]));
		assert (isset ($this->__aInternals [$sName] ['Type']));
		assert (strtolower ($this->__aInternals [$sName] ['Type']) === 'array');
		array_push ($this->__aInternals [$sName] ['Data'], $mData);
		return $this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sComparison
	 */
	protected function __assert ($sName, $sComparison) {
		if (ASSERT_ACTIVE && $this->__is ($sName, $sComparison) === false) {
			return $this->__fail ('Assertation failed');
		}
		return $this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sMethod
	 * @param array $aArguments
	 * @return mixed
	 * @throws Appenda_Property_Exception
	 */
	public function __call ($sMethod, array $aArguments) {
		$aMatches = array ();
		
		if (preg_match ('/^(assert|is)(.+?)(Empty|False|NotEmpty|NotNull|Null|True)$/', $sMethod, $aMatches)) {
			list ($sMethod, $sCommand, $sName, $sComparison) = $aMatches;
		} elseif (preg_match ('/^(get|has|set)(.+)$/', $sMethod, $aMatches)) {
			list ($sMethod, $sCommand, $sName) = $aMatches;
		} elseif (preg_match ('/^(appendTo|count|prependTo)(.+)$/', $sMethod, $aMatches)) {
			list ($sMethod, $sCommand, $sName) = $aMatches;
		} elseif (preg_match ('/^(is)(.+?)$/', $sMethod, $aMatches)) {
			list ($sMethod, $sCommand, $sName) = $aMatches;
			$sComparison == 'True';
		} else {
			assert (false);
			return $this->__fail ('Unknown magic method, method=' . $sMethod);
		}
		
		// Debugging
		assert (isset ($this->__aInternals));
		assert (isset ($this->__aInternals [$sName]));
		assert (isset ($this->__aInternals [$sName] ['Type']));
		
		$sType = strtolower ($this->__aInternals [$sName] ['Type']);
		
		// Handle the correct command
		switch ($sCommand) {
			case 'assert' :
				return $this->__assert ($sName, $sComparison);
			
			case 'count' :
				assert ($sType === 'array');
				return count ($this->__aInternals [$sName] ['Data']);
			
			case 'get' :
				return $this->__callget ($sName, array_shift ($aArguments));
			
			case 'has' :
				return $this->__has ($sName);
			
			case 'is' :
				return $this->__is ($sName, $sComparison);
			
			case 'set' :
				return $this->__callset ($sName, array_shift ($aArguments), array_shift ($aArguments));
			
			// Special handling for arrays
			case 'appendTo' :
				return $this->__appendTo ($sName, array_shift ($aArguments));
			
			case 'prependTo' :
				return $this->__prependTo ($sName, array_shift ($aArguments));
			
			default :
				throw new Appenda_Property_Exception ('Should NEVER ever get here!');
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param mixed|null $mData
	 * @return mixed
	 */
	protected function __callget ($sName, $mData = null) {
		assert (isset ($this->__aInternals));
		assert (isset ($this->__aInternals [$sName]));
		assert (isset ($this->__aInternals [$sName] ['Type']));
		
		$sInternalType = strtolower ($this->__aInternals [$sName] ['Type']);
		
		if ($sInternalType === 'array' && is_null ($mData) === false) {
			return $this->__aInternals [$sName] ['Data'] [$mData];
		} else {
			return $this->__aInternals [$sName] ['Data'];
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param mixed $mData
	 * @param mixed|null $mRelatedData
	 * @return Appenda_Property_Atom
	 */
	protected function __callset ($sName, $mData, $mRelatedData = null) {
		assert (is_string ($sName));
		assert (isset ($this->__aInternals));
		assert (isset ($this->__aInternals [$sName]));
		assert (isset ($this->__aInternals [$sName] ['Type']));
		
		$sInternalType = strtolower ($this->__aInternals [$sName] ['Type']);
		
		switch ($sInternalType) {
			case 'string' :
				$mData = is_null ($mData) ? '' : $mData;
				$bValidated = is_string ($mData);
				break;
			
			case 'integer' :
				$mData = is_null ($mData) ? 0 : $mData;
				$bValidated = is_integer ($mData);
				break;
			
			case 'boolean' :
				$mData = is_null ($mData) ? false : $mData;
				$bValidated = is_bool ($mData);
				break;
			
			case 'double' :
				$mData = is_null ($mData) ? 0.0 : $mData;
				$bValidated = is_double ($mData);
				break;
			
			case 'array' :
				$mData = is_null ($mData) ? array () : $mData;
				$bValidated = is_null ($mRelatedData) && is_array ($mData);
				break;
			
			case 'mixed' :
				$bValidated = true; // no checks on mixed type
				break;
			
			default :
				$bValidated = $mData instanceof $sInternalType;
				$bValidated = $bValidated || is_null ($mData); // Objects allowed to be null
				break;
		}
		
		if ($bValidated === false) {
			$sMessage = 'Type and/or class not validated';
			$sMessage .= ', name=' . $sName;
			$sMessage .= ', data=' . strval ($mData);
			$sMessage .= ', relatedData=' . strval ($mRelatedData);
			$sMessage .= ', type=' . gettype ($mData);
			$sMessage .= ', class=' . get_class ($mData);
			$sMessage .= ', internalType=' . $this->__aInternals [$sName] ['Type'];
			throw new Appenda_Property_Exception ($sMessage);
		}
		
		if ($sInternalType === 'array' && is_null ($mRelatedData) === false) {
			$this->__aInternals [$sName] ['Data'] [$mData] = $mRelatedData;
		} else {
			$this->__aInternals [$sName] ['Data'] = $mData;
		}
		
		return $this;
	}
	
	/**
	 * @param string $sName
	 * @return boolean
	 */
	protected function __has ($sName) {
		assert (is_string ($sName));
		return isset ($this->__aInternals [$sName]);
	}
	
	/**
	 * @param string $sName
	 * @param string $sComparison
	 * @return boolean
	 */
	protected function __is ($sName, $sComparison) {
		assert (is_string ($sName));
		assert (is_string ($sComparison));
		
		$mData = $this->__callget ($sName); // throws if missing
		

		switch ($sComparison) {
			case 'Empty' :
				return empty ($mData);
			
			case 'NotEmpty' :
				return empty ($mData) === false;
				break;
			
			case 'Null' :
				return is_null ($mData);
			
			case 'NotNull' :
				return is_null ($mData) === false;
			
			case 'True' :
				return $mData === true;
			
			case 'False' :
				return $mData === false;
			
			default :
				throw new Appenda_Property_Exception ('Unrecognized option.');
		}
	}
	
	/**
	 * @param string $sMessage
	 * @return boolean False always, if no exception thrown
	 * @throws Appenda_Property_Exception
	 */
	public function __fail ($sMessage) {
		if ($this->__bExceptionsEnabled) {
			throw new Appenda_Property_Exception ($sMessage);
		} else {
			return false;
		}
	}
	
	/**
	 * @deprecated
	 */
	final public function __get ($sName) {
		return $this->__callget ($sName);
	}
	
	/**
	 * Add a data element to the end of an array
	 *
	 * @param string $sName
	 * @param mixed $mData
	 * @return Appenda_Property_Atom
	 */
	protected function __prependTo ($sName, $mData) {
		assert (isset ($this->__aInternals));
		assert (isset ($this->__aInternals [$sName]));
		assert (isset ($this->__aInternals [$sName] ['Type']));
		assert (strtolower ($this->__aInternals [$sName] ['Type']) === 'array');
		array_unshift ($this->__aInternals [$sName] ['Data'], $mData);
		return $this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sType
	 * @return Appenda_Property_Atom
	 */
	protected function __reset ($sName, $sType) {
		assert (is_string ($sName));
		assert (is_string ($sType));
		
		if (isset ($this->__aInternals [$sName]) === false) {
			$mData = null;
		} else {
			assert (isset ($this->__aInternals [$sName] ['Type']));
			
			$sOriginalType = strtolower ($this->__aInternals [$sName] ['Type']);
			$mOriginalData = $this->__aInternals [$sName] ['Data'];
			$sTargetType = strtolower ($sType);
			
			switch (true) {
				// Array -> String: we need to serialize it.
				case $sTargetType === 'string' && $sOriginalType === 'array' :
					$mData = serialize ($mOriginalData);
					break;
				
				// Boolean -> String: convert to a textual representation of the boolean
				case $sTargetType === 'string' && $sOriginalType === 'boolean' :
					$mData = $mOriginalData ? 'true' : 'false';
					break;
				
				// Object -> String: serialize it.
				case $sTargetType === 'string' && $sOriginalType instanceof stdClass :
					$mData = serialize ($mOriginalData);
					break;
				
				// * -> String: use the built-in string conversion.
				case $sTargetType === 'string' :
					$mData = strval ($mOriginalData);
					break;
				
				case $sTargetType === 'array' :
					$mData = (array) $mOriginalData;
					break;
				
				// String -> Boolean: convert thetext to a true/false
				case $sTargetType === 'boolean' && $mOriginalData === 'TRUE' :
				case $sTargetType === 'boolean' && $mOriginalData === 'true' :
					$mData = true;
					break;
				
				// String -> Boolean: convert thetext to a true/false
				case $sTargetType === 'boolean' && $mOriginalData === 'FALSE' :
				case $sTargetType === 'boolean' && $mOriginalData === 'false' :
					$mData = false;
					break;
				
				// * -> Boolean: simple existance test
				case $sTargetType === 'boolean' :
					$mData = $mOriginalData ? true : false;
					break;
				
				case $sTargetType === 'integer' :
					$mData = intval ($mOriginalData);
					break;
				
				case $sTargetType === 'double' :
					$mData = doubleval ($mOriginalData);
					break;
				
				case $sOriginalType === 'string' :
					$mData = unserialize ($mOriginalData);
					$mData = $mData instanceof $sTargetType || gettype ($mData) === $sTargetType ? $mData : null;
					break;
				
				default :
					$mData = null;
					break;
			}
		}
		
		$this->__aInternals [$sName] ['Type'] = $sType;
		$this->__aInternals [$sName] ['Data'] = $mData;
		return $this;
	}
	
	/**
	 * @deprecated
	 * @param string $sName
	 * @param mixed $mData
	 */
	final public function __set ($sName, $mData) {
		return $this->__callset ($sName, $mData);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	public function describe () {
		return $this->__aInternals;
	}
	
	/**
	 * Sets whether exceptions are thrown by default or not.
	 *
	 * @param boolean $bEnabled
	 * @return Appenda_Property_Atom
	 */
	public function enableExceptions ($bEnabled) {
		$this->__bExceptionsEnabled = $bEnabled;
		return $this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sType
	 * @param mixed $mDefaultValue
	 * @return Appenda_Property_Atom
	 */
	static public function newInstance ($sClassName) {
		assert (is_string ($sClassName));
		
		if (isset (self::$__aTemplates [$sClassName])) {
			return clone self::$__aTemplates [$sClassName];
		} else {
			return new $sClassName ();
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sName
	 * @param string $sType
	 * @return Appenda_Property_Atom
	 */
	public function register ($sName, $sType, $mData = null) {
		assert (is_string ($sName));
		assert (is_string ($sType));
		assert (isset ($this->__aInternals [$sName]) === false);
		
		// Change the internal type, set the data
		if ($this->__reset ($sName, $sType) === false) {
			return $this->__fail ('Failed to reset internal type');
		}
		if ($this->__callset ($sName, $mData) === false) {
			return $this->__fail ('Failed to set the name and data');
		}
		return $this;
	}
	
	/**
	 * @param object
	 * @return Appenda_Property_Atom
	 */
	public function registerInstance ($sClassName) {
		assert (is_string ($sClassName));
		return self::$__aTemplates [$sClassName] = clone $this;
	}
	
	/**
	 * Copy the internal data from another {@link Appenda_Property_Atom}.
	 *
	 * @param Appenda_Property_Atom $oProperty
	 */
	public function setTo (Appenda_Property_Atom $oProperty) {
		$this->__aInternals = $oProperty->__aInternals;
		$this->__bExceptionsEnabled = $oProperty->__bExceptionsEnabled;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	public function toArray () {
		$aProperties = array ();
		
		foreach ($this->__aInternals as $sName => $aData) {
			$aProperties [$sName] = $aData ['Data'];
		}
		
		return $aProperties;
	}
	
	/**
	 * @var array
	 */
	private $__aInternals = array ();
	
	/**
	 * @var array
	 */
	private static $__aTemplates = array ();
	
	/**
	 * @var boolean
	 */
	private $__bExceptionsEnabled = true;
}

