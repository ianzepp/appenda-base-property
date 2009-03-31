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

require_once "Appenda/Property/Atom.php";

final class Appenda_Property_Filter extends Appenda_Property_Atom {
	/**
	 * @var string
	 */
	const INVERSE_REGEX = 'INVERSE_REGEX';
	
	/**
	 * @var string
	 */
	const MAXIMUM = 'MAXIMUM';
	
	/**
	 * @var string
	 */
	const MINIMUM = 'MINIMUM';
	
	/**
	 * @var string
	 */
	const REGEX = 'REGEX';
	
	/**
	 * @var string
	 */
	const UNSIGNED = 'UNSIGNED';
	
	/**
	 * Enter description here...
	 */
	public function __construct () {
		$this->register ('Type', 'String');
		$this->register ('Data', 'Mixed');
		$this->registerInstance (__CLASS__);
	}
	
	/**
	 * @param string $sClassName
	 * @return 
	 */
	static public function newInstance ($sFilterType, $mFilterData = null) {
		$oInstance = parent::newInstance (__CLASS__);
		$oInstance->setType ($sFilterType);
		$oInstance->setData ($mFilterData);
		return $oInstance;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param mixed $mData
	 * @return boolean
	 */
	public function evaluate ($mData) {
		switch (strtoupper ($this->getType ())) {
			case self::REGEX :
				return preg_match ($this->getData (), $mData) > 0;
			
			case self::INVERSE_REGEX :
				return preg_match ($this->getData (), $mData) == 0;
			
			case self::MAXIMUM :
				return $this->__maximum ($mData, $this->getData ());
			
			case self::MINIMUM :
				return $this->__minimum ($mData, $this->getData ());
			
			case self::UNSIGNED :
				return $this->__minimum ($mData, 0);
			
			default :
				return false;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param mixed $mData
	 * @param mixed $mLimit
	 * @return boolean
	 */
	protected function __maximum ($mData, $mLimit) {
		switch (gettype ($mData)) {
			case 'string' :
				return strlen ($mData) <= $mLimit;
			
			case 'array' :
				return count ($mData) <= $mLimit;
			
			default :
				return $mData <= $mLimit;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param mixed $mData
	 * @param mixed $mLimit
	 * @return boolean
	 */
	protected function __minimum ($mData, $mLimit) {
		switch (gettype ($mData)) {
			case 'string' :
				return strlen ($mData) >= $mLimit;
			
			case 'array' :
				return count ($mData) >= $mLimit;
			
			default :
				return $mData >= $mLimit;
		}
	}
}

