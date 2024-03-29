<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */
 
/**
 * Customer Credit extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */
 
class MageWorx_CustomerCredit_Model_Code_Generator extends Mage_Core_Model_Abstract
{
    static protected $_alphabet = array(
        'alphabet' => 'abcdefghijklmnopqrstuvwxyz' ,
        'numbers' => '1234567890' ,
        'symbols' => '`~!@#$%^&*()_+-=[];\,./{}:|<>?'
    );
    
    protected function _construct()
    {
        $this->setLength(32)->setUseSmall(false)
        ->setUseBig(true)->setBlockSize(0)->setUseSymbols(false)
        ->setUseNumbers(true)->setAlphabet(self::$_alphabet)
        ->setExclude(array())->setBlockSeparator('-');
    }
    
    /**
     * 
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    protected function _reset()
    {
        if ($this->hasCompiledAlphabet())
        {
            $this->unsCompiledAlphabet();
        }
        return $this;
    }
    
    public function getCompiledAlphabet()
    {
        if (!$this->hasCompiledAlphabet())
        {
            $this->_compileAlphabet();
        }
        return $this->getData('compiled_alphabet');
    }
    
    /**
     * 
     * @param $separator
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setBlockSeparator( $separator )
    {
        return $this->_reset()->setData('block_separator',$separator);
    }
    
    /**
     * 
     * @param $exclude
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setExclude( array $exclude )
    {
        return $this->_reset()->setData('exclude',$exclude);
    }
    
    /**
     * 
     * @param $alphabet
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setAlphabet( array $alphabet )
    {
        return $this->_reset()->setData('alphabet',$alphabet);
    }
    
    /**
     * 
     * @param $length
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setLength( $length )
    {
        return $this->_reset()->setData('length',(int)$length);
    }
    
    /**
     * 
     * @param $size
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setBlockSize( $size )
    {
        return $this->_reset()->setData('block_size',$size);
    }
    
    /**
     * 
     * @param $use
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setUseSmall( $use = true )
    {
        return $this->_reset()->setData('use_small',$use);
    }
    
    /**
     * 
     * @param $use
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setUseSymbols( $use = true )
    {
        return $this->_reset()->setData('use_symbols',$use);
    }
    
    /**
     * 
     * @param $use
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setUseBig( $use = true )
    {
        return $this->_reset()->setData('use_big',$use);
    }
    
    /**
     * 
     * @param $use
     * @return MageWorx_CustomerCredit_Model_Code_Generator
     */
    public function setUseNumbers( $use = true )
    {
        return $this->_reset()->setData('use_numbers',$use);
    }
    
    protected function _compileAlphabet()
    {
        $symbols = array(); 
        $tmp = array();
        $this->_parseAlphabetLine($this->alphabet['alphabet'],$tmp);
        $symbols = $this->getUseSmall() ? $tmp : array();
        if ($this->getUseBig())
        {
            foreach ($tmp as $value)
            {
                $value = strtoupper($value);
                $symbols[$value] = $value;
            }
        }
        if ($this->getUseNumbers())
        {
            $this->_parseAlphabetLine($this->alphabet['numbers'],$symbols);
        }
        if ($this->getUseSymbols())
        {
            $this->_parseAlphabetLine($this->alphabet['symbols'],$symbols);
        }
        $this->setCompiledAlphabet($symbols);
    }
    
    public function getAlphabetSize()
    {
        return sizeof($this->getCompiledAlphabet());
    }
    
    protected function _getSequence()
    {
        $sequence = array();
        $length = $this->getLength();
        $latest = $this->getAlphabetSize()-1;
        for ($i = 0 ; $i < $length ; ++$i)
        {
            $sequence[] = mt_rand(0,$latest);
        }
        return $sequence;
    }
    
    public function generate()
    {
        $symbols = $this->getCompiledAlphabet();
        $sequence = $this->_getSequence();
        $length = sizeof($sequence);
        $blockSize = $this->getBlockSize();
        if (is_array($blockSize))
        {
            $currBlockSize = $blockSize ? (int)array_shift($blockSize) : 0;
        }
        else
        {
            $currBlockSize = (int)$blockSize;
            $blockSize = $currBlockSize;
        }
        $result = '';
        $separator = $this->getBlockSeparator();
        $keys = array_keys($symbols);
        for ($i = 0 ; $i < $length ; ++$i )
        {
            $result .= $symbols[$keys[array_shift($sequence)]];
            if ($currBlockSize > 0)
            {
                if (--$currBlockSize == 0 && ($i + 1 < $length) && $blockSize)
                {
                    $result .= $separator;
                    $currBlockSize = is_array($blockSize) ? (int)array_shift($blockSize) : $blockSize;
                }
            }
        }
        return $result;
    }
    
    protected function _parseAlphabetLine( $alphabet , &$symbols )
    {
        $len = strlen($alphabet);
        for ($i = 0 ; $i< $len ; ++$i)
        {
            $symbol = substr($alphabet,$i,1);
            $symbols[$symbol] = $symbol;
        }
    }
}