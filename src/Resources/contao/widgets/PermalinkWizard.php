<?php
/*
 * Permalink extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-permalink
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PermalinkBundle\Contao;


/**
 * Provide methods to handle the permalink wizard
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
	 * Trim and validate values
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
			$this->varValue = \StringUtil::specialchars($this->varValue);
		}
		catch (\InvalidArgumentException $e) {}

		$url = \System::getContainer()->get('contao.permalink.generator')->getUrl($this->objDca);
		$editMode = ($this->hasErrors() || null === $url->getpath());
	
		if ('root' == $this->objDca->activeRecord->type)
		{
			// Root pages don't have an editable guid but we can show the domain anyway
			$return = '<div id="ctrl_' . $this->strId . '" class="wizard"><span class="tl_permalink" style="display:inline-block;white-space:nowrap;margin:4px 0;padding:5px 0 6px;"><span class="tl_gray">' . $url->getScheme() . '://' . $url->getHost() . '/</span></span></div>';
		}
		else
		{
			$return = '<div class="tl_permalink">';
	
			// Host
			$return .= '<span class="tl_guid host"><span class="tl_gray">' . $url->getScheme() . '://' . $url->getHost() . '/</span></span>';
			
			if (!$editMode)
			{
				$return .= '<span id="view_' . $this->strId . '">';

				// Path
				$return .= '<span id="test" class="tl_guid path" ondblclick="
		var r=document.createRange(); 
		r.setStart($$(\'.tl_guid.host\')[0],0);
		r.setEnd($$(\'.tl_guid.path\')[0],1);
		var s=window.getSelection();
		s.removeAllRanges();
		s.addRange(r);	
		">' . $url->getpath() . '<span class="tl_gray">' . $url->getSuffix() . '</span></span>';

				// Edit button
				$return .= '<button type="button" onclick="$(\'view_' . $this->strId . '\').addClass(\'hidden\');$(\'edit_' . $this->strId . '\').removeClass(\'hidden\')" class="tl_submit">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';

				$return .= '</span>';
			}
			
			$return .= '<span id="edit_' . $this->strId . '"' . ($editMode ? '' : ' class="hidden"') . '">';

			// Input field
			$return .= sprintf('<input type="text" name="%s" id="ctrl_%s" class="tl_text%s" style="vertical-align:inherit" value="%s"%s data-value="%s" onfocus="Backend.getScrollOffset()">',
							$this->strName,
							$this->strId,
							(($this->strClass != '') ? ' ' . $this->strClass : ''),
							$this->value,
							$this->getAttributes(),
							$this->value);
			
			// Save button
			$return .= '<span style="display: inline-block"><button type="submit" class="tl_submit">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';

			// Cancel button
			$return .= ' <button type="button" onclick="$(\'view_' . $this->strId . '\').removeClass(\'hidden\');$(\'edit_' . $this->strId . '\').addClass(\'hidden\');$(\'ctrl_' . $this->strId . '\').value=$(\'ctrl_' . $this->strId . '\').get(\'data-value\')" class="tl_submit"' . ($editMode ? 'disabled' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['cancelBT'] . '</button></span>';

			$return .= '</span></div>';
		}

		return $return;
	}
}
