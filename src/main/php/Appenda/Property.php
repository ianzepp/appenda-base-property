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

final class Appenda_Property extends Appenda_Property_Atom {
	/**
	 * @return Appenda_Property
	 */
	static public function newInstance () {
		return parent::newInstance (__CLASS__);
	}
	
	/**
	 * Constructor
	 *
	 */
	public function __construct () {
		$this->register ('Alias', 'String');
		$this->register ('Aliased', 'Boolean');
		$this->register ('Cached', 'Boolean');
		$this->register ('Callback', 'Array');
		$this->register ('Compressed', 'Boolean');
		$this->register ('DefaultValue', 'Mixed', null);
		$this->register ('Filterable', 'Boolean', true);
		$this->register ('Filters', 'Array');
		$this->register ('Label', 'String');
		$this->register ('Name', 'String');
		$this->register ('Nullable', 'Boolean', true);
		$this->register ('Triggerable', 'Boolean', true);
		$this->register ('Triggers', 'Array');
		$this->register ('Type', 'String');
		$this->register ('Updateable', 'Boolean', true);
		$this->register ('Updated', 'Boolean');
		$this->register ('Value', 'Mixed');
		$this->registerInstance (__CLASS__);
		
	// Future properties to implement
	// $this->register( 'AutoNumber', 'Boolean', false );
	// $this->register( 'ByteLength', 'Integer', 0 );
	// $this->register( 'Calculated', 'Boolean', false );
	// $this->register( 'CaseSensitive', 'Boolean', false );
	// $this->register( 'Createable', 'Boolean', true );
	// $this->register( 'DefaultedOnCreate', 'Boolean', true );
	// $this->register( 'Deleteable', 'Boolean', true );
	// $this->register( 'Digits', 'Integer', 0 );
	// $this->register( 'IdLookup', 'Boolean', false );
	// $this->register( 'Length', 'Integer', 0 );
	// $this->register( 'Precision', 'Integer', 0 );
	// $this->register( 'Scale', 'Integer', 0 );
	// $this->register( 'Searchable', 'Boolean', true );
	// $this->register( 'SoapType', 'String', '' );
	// $this->register( 'Sortable', 'Boolean', true );
	// $this->register( 'Unique', 'Boolean', false );
	}
	
	/**
	 * Override the xxxValue methods
	 */
	public function getValue ($mData = null) {
		//-------------------------------------------------------------------
		// Updated value, whether a callback or not.
		//-------------------------------------------------------------------
		if ($this->isUpdatedTrue ()) {
			$mReturnData = $this->__callget ('Value', $mData);
			
			if ($this->isCompressedTrue ()) {
				return gzuncompress ($mReturnData);
			} else {
				return $mReturnData;
			}
		}
		
		//-------------------------------------------------------------------
		// Default value, but no callback assigned
		//-------------------------------------------------------------------
		if ($this->isCallbackEmpty ()) {
			$mReturnData = $this->__callget ('DefaultValue', $mData);
			
			if ($this->isCompressedTrue ()) {
				return gzuncompress ($mReturnData);
			} else {
				return $mReturnData;
			}
		}
		
		//-------------------------------------------------------------------
		// CALLBACK & CACHING
		//-------------------------------------------------------------------
		$mReturnData = call_user_func ($this->getCallback (), $mData);
		
		if ($this->isCachedTrue ()) {
			$this->setValue ($mReturnData);
		}
		
		//-------------------------------------------------------------------
		// CALLBACK Result
		//-------------------------------------------------------------------
		return $mReturnData;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param mixed $mData
	 * @param mixed $mRelatedData
	 * @return Appenda_Property
	 */
	public function setValue ($mData, $mRelatedData = null) {
		assert ($this->isUpdateableTrue ());
		assert ($this->getNullable () || is_null ($mData) === false);
		
		//-------------------------------------------------------------------
		// FILTERS
		//-------------------------------------------------------------------
		if ($this->isFilterableTrue ()) {
			foreach ($this->getFilters () as $oFilter) {
				if ($oFilter->evaluate ($mData, $mRelatedData)) {
					continue;
				}
				
				// Filter failed. Figure out how to proceed?
				if (ASSERT_BAIL) {
					$sMessage = 'Filter evaluation failed';
					$sMessage .= ', name=' . $this->getName ();
					$sMessage .= ', currentValue=' . $this->getValue ();
					$sMessage .= ', updatedValue=' . $mData;
					$sMessage .= ', updatedValueType=' . gettype ($mData);
					$sMessage .= ', filterType=' . $oFilter->getType ();
					$sMessage .= ', filterData=' . $oFilter->getData ();
					throw new Appenda_Property_Filter_Exception ($sMessage);
				} else {
					return false;
				}
			}
		}
		
		//-------------------------------------------------------------------
		// COMPRESSION
		//-------------------------------------------------------------------
		$bCompressed = $this->isCompressedTrue ();
		$sInternalType = strtolower ($this->getType ());
		
		switch (true) {
			case $bCompressed && $sInternalType === 'array' && is_string ($mRelatedData) :
				$mProcessedData = $mData;
				$mProcessedRelatedData = gzcompress ($mRelatedData);
				break;
			
			case $bCompressed && $sInternalType === 'string' :
			case $bCompressed && $sInternalType === 'mixed' && is_string ($mData) :
				$mProcessedData = gzcompress ($mData);
				$mProcessedRelatedData = $mRelatedData;
				break;
			
			default :
				$mProcessedData = $mData;
				$mProcessedRelatedData = $mRelatedData;
				break;
		}
		
		//-------------------------------------------------------------------
		// Internal Value and Updated status
		//-------------------------------------------------------------------
		$this->__callset ('Value', $mProcessedData, $mProcessedRelatedData);
		$this->__callset ('Updated', true);
		
		//-------------------------------------------------------------------
		// TRIGGERS
		//-------------------------------------------------------------------
		if ($this->isTriggerableTrue ()) {
			foreach ($this->getTriggers () as $aTriggerCallback) {
				call_user_func_array ($aTriggerCallback, array (
					$mData, 
					$mRelatedData));
			}
		}
		
		return $this;
	}
	
	/**
	 * Override the xxxType methods to redefine the internal value types.
	 */
	public function setType ($sType) {
		assert (is_string ($sType));
		
		parent::__callset ('Type', $sType);
		parent::__reset ('DefaultValue', $sType);
		parent::__reset ('Value', $sType);
	}
}

