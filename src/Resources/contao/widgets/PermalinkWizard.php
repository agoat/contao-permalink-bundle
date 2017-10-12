<?php
 
 /**
 * Contao Open Source CMS - Permalink extension
 *
 * Copyright (c) 2016 Arne Stappen (aGoat)
 *
 *
 * @package   contentblocks
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\Permalink;


/**
 * Provide methods to handle text fields.
 *
 * @property integer $maxlength
 * @property boolean $mandatory
 * @property string  $placeholder
 * @property boolean $multiple
 * @property boolean $hideInput
 * @property integer $size
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PermalinkWizard extends \Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Add a for attribute
	 * @var boolean
	 */
	protected $blnForAttribute = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Disable the for attribute if the "multiple" option is set
	 *
	 * @param array $arrAttributes
	 */
	public function __construct($arrAttributes=null)
	{
		parent::__construct($arrAttributes);

	}


	/**
	 * Add specific attributes
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		 /** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');
		
		if ($error = $objSessionBag->get('permalink_error'))
		{
			$objSessionBag->set('permalink_error', false);
			$this->addError($error);
		}

		switch ($strKey)
		{
			case 'maxlength':
				if ($varValue > 0)
				{
					$this->arrAttributes['maxlength'] = $varValue;
				}
				break;

			case 'mandatory':
				if ($varValue)
				{
					$this->arrAttributes['required'] = 'required';
				}
				else
				{
					unset($this->arrAttributes['required']);
				}
				parent::__set($strKey, $varValue);
				break;

			case 'placeholder':
				$this->arrAttributes['placeholder'] = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Trim values
	 *
	 * @param mixed $varInput
	 *
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		if (is_array($varInput))
		{
			return parent::validator($varInput);
		}

		try
		{
			$varInput = \Idna::encodeUrl($varInput);
		}
		catch (\InvalidArgumentException $e) {}

		return parent::validator($varInput);
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		// Hide the Punycode format (see #2750)
		try
		{
			$this->varValue = \Idna::decodeUrl($this->varValue);
		}
		catch (\InvalidArgumentException $e) {}

		$context = \System::getContainer()->get('permalink.generator')->getContextForTable($this->objDca->table);
		
		$activeRecord = $this->objDca->activeRecord;
		


		$schema = \System::getContainer()->get('permalink.generator')->getSchema($context, $activeRecord->id);
		$host = \System::getContainer()->get('permalink.generator')->getHost($this->objDca);
		$alias = $activeRecord->alias;
		$suffix = \System::getContainer()->getParameter('contao.url_suffix');

		
		
		
		
		$url = parse_url(\System::getContainer()->get('permalink.generator')->getAbsoluteUrl($context, $activeRecord->id));
		
dump($url);		
		// Don't show the index keyword
		if ('index' == $url)
		{
			$alias = '';
		}
		
		if ('root' == $activeRecord->type)
		{
			// Root pages don't have a (editable) guid but we can show the domain anyway
			$return = '<div id="ctrl_' . $this->strId . '" class="wizard"><span class="" style="display:inline-block;white-space:nowrap;vertical-align:middle;margin: 2px 2% 2px 0;padding: 7px 0 9px;"><span class="tl_gray">' . $schema . $host . '/</span></span></div>';
		}
		else
		{
			// host
			$return = '<div class="" style="display:inline-block;white-space:nowrap;vertical-align:middle;padding: 5px 0;">
			<span class="tl_gray">' . $schema . $host . '/</span>';

			if (!$this->hasErrors())
			{
				// alias
				$return .= '<span class="view"><span style="display:inline-block;margin: 2px 2% 2px 0;padding: 7px 0 9px;">' . $alias . $suffix . '</span>';
				// edit button
				$return .= '<a onclick="$$(\'.view\').addClass(\'hidden\');$$(\'.edit\').removeClass(\'hidden\')" class="tl_submit">Edit</a></span>';
				
				
			}
			
			$return .= '<span id="edit' . $this->strId . '" class="edit' . (!$this->hasErrors() ? ' hidden' : '') . '">';

			// input
			$return .= sprintf(' <input type="text" name="%s" id="xctrl_%s" class="tl_text%s" style="margin-right:1%%" value="%s"%s onfocus="Backend.getScrollOffset()">%s',
							$this->strName,
							$this->strId,
							(($this->strClass != '') ? ' ' . $this->strClass : ''),
							\StringUtil::specialchars($this->varValue),
							$this->getAttributes(),
							$this->wizard);
			// save button
			$return .= '<a onclick="$(this).getParent(\'form\').submit();" class="tl_submit">Save</a></span>';
			//$return .= '<a onclick="$$(\'.view\').removeClass(\'hidden\');$$(\'.edit\').addClass(\'hidden\')" class="tl_submit">Save</a></span>';

			$return .= '</div>';
			$return .= '<style>



</style>';
	
		}



						
		// Domain | alias + editbutton | inputfield + savebutton (hidden)
		// Domain | alias + editbutton (hidden) | inputfield | savebutton
		
		
		return $return;
	}


}