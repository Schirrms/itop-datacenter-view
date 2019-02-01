<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\DatacenterView\Common;

use DBObject;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use AttributeExternalKey;
use MetaModel;
use utils;
use Combodo\iTop\Renderer\RenderingOutput;
use Molkobain\iTop\Extension\HandyFramework\Common\Helper\UIHelper;
use Molkobain\iTop\Extension\DatacenterView\Common\Helper\ConfigHelper;

/**
 * Class DatacenterView
 *
 * @package Molkobain\iTop\Extension\DatacenterView\Common
 */
class DatacenterView
{
	const ENUM_ASSEMBLY_TYPE_MOUNTED = 'mounted';
	const ENUM_ASSEMBLY_TYPE_UNMOUNTED = 'unmounted';

	const ENUM_ELEMENT_TYPE_RACK = 'rack';
	const ENUM_ELEMENT_TYPE_ENCLOSURE = 'enclosure';
	const ENUM_ELEMENT_TYPE_DEVICE = 'device';
	
	const ENUM_ENDPOINT_OPERATION_RENDERTAB = 'render_tab';

	/** @var \DBObject $oObject */
	protected $oObject;
	/** @var string $sType */
	protected $sType;
	/** @var array $aElementClasses */
	protected $aElementClasses;

	public function __construct(DBObject $oObject)
	{
		$this->oObject = $oObject;
		$this->sType = static::FindObjectType($this->oObject);
	}

	/**
	 * @return \DBObject
	 */
	public function GetObject()
	{
		return $this->oObject;
	}

	/**
	 * Returns the object's type (see ENUM_ELEMENT_TYPE_XXX constants)
	 *
	 * @return string
	 */
	public function GetType()
	{
		return $this->sType;
	}

	/**
	 * @return string
	 */
	public function GetObjectClass()
	{
		return get_class($this->oObject);
	}

	/**
	 * @return int
	 */
	public function GetObjectId()
	{
		return $this->oObject->GetKey();
	}

	/**
	 * Returns the endpoint url with optional $aParams.
	 * Note: that object's class & id are always passed as parameters.
	 *
	 * @param array $aParams Array of key => value to pass in the endpoint as parameters
	 *
	 * @return string
	 */
	public function GetEndpoint($aParams = array())
	{
		$aQueryStringParams = array(
			'class=' . $this->GetObjectClass(),
			'id=' . $this->GetObjectId(),
		);

		foreach($aParams as $sKey => $sValue)
		{
			$aQueryStringParams[] = $sKey . '=' . $sValue;
		}

		return utils::GetAbsoluteUrlModulesRoot() . ConfigHelper::GetModuleCode() . '/console/ajax.render.php?' . implode('&', $aQueryStringParams);
	}

	/**
	 * Returns the whole view (legend, elements, ...) as fragments (HTML, JS files, JS inline, CSS files, CSS inline)
	 *
	 * @return \Combodo\iTop\Renderer\RenderingOutput
	 * @throws \DictExceptionMissingString
	 */
	public function Render()
	{
		$oOutput = new RenderingOutput();

		$sJSWidgetName = 'datacenter_' . $this->sType . '_view';
		$sJSWidgetDataJSON = $this->GetDataForJSWidget(true);

		// Add markup
		$sLegendTitle = Dict::S('Molkobain:DatacenterView:Legend:Title');

		// TODO: Retrieve options from DatacenterView class and make it extensible
		$sOptionsTitle = Dict::S('Molkobain:DatacenterView:Options:Title');
		$sToggleObsoleteOptionLabel = Dict::S('Molkobain:DatacenterView:Options:Option:ShowObsolete');
		$sToggleObsoleteOptionInput = UIHelper::MakeToggleButton();

		// Note: We could split this in protected methods for overloading (PrepareHtml, PrepareJs, ...)
		$oOutput->AddHtml(
<<<EOF
<div class="molkobain-datacenter-view-container" data-portal="backoffice">
	<div class="mdv-controls">
		<div class="mdv-legend mhf-panel">
			<div class="mhf-p-header">
				<span class="mhf-ph-icon"><span class="fa fa-list"></span></span>
				<span class="mhf-ph-title">{$sLegendTitle}</span>
			</div>
			<div class="mhf-p-body">
				<ul>
				</ul>
			</div>
		</div>
		<div class="mdv-options mhf-panel">
			<div class="mhf-p-header">				
				<span class="mhf-ph-icon"><span class="fa fa-cog"></span></span>
				<span class="mhf-ph-title">{$sOptionsTitle}</span>
			</div>
			<div class="mhf-p-body">
				<ul>
					<li>
						<span>{$sToggleObsoleteOptionLabel}</span>
						<span class="mhf-pull-right">{$sToggleObsoleteOptionInput}</span>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="mdv-views">
	</div>
	
	<div class="mdv-unmounted mhf-panel">
	</div>
	
	<div class="mhf-templates">
		<!-- Legend item template -->
		<li class="mdv-legend-item" data-class="" data-count="">
			<span class="mdv-li-title"></span>
			<span class="mdv-li-count mhf-pull-right"></span>
		</li>
		
		<!-- Rack panel template -->
		<div class="mdv-rack-panel" data-class="" data-id="" data-code="" data-name="">
			<div class="mdv-rp-title"></div>
			<div class="mdv-rp-view">
				<div class="mdv-rpv-top"></div>
				<div class="mdv-rpv-middle"></div>
				<div class="mdv-rpv-bottom"></div>
			</div>
		</div>
		
		<!-- Rack unit template -->
		<div class="mdv-rack-unit" data-unit-number="">
			<div class="mdv-ru-left"></div>
			<div class="mdv-ru-slot"></div>
			<div class="mdv-ru-right"></div>
		</div>
		
		<!-- Enclosure template -->
		<div class="mdv-enclosure" data-class="" data-id="" data-name="" data-rack-id="" data-position-v="" data-position-p="">
		</div>
		
		<!-- Enclosure unit template -->
		<div class="mdv-enclosure-unit" data-unit-number="">
			<div class="mdv-eu-left"></div>
			<div class="mdv-eu-slot"></div>
			<div class="mdv-eu-right"></div>
		</div>
		
		<!-- Device template -->
		<div class="mdv-device" data-class="" data-id="" data-name="" data-rack-id="" data-enclosure-id="" data-position-v="" data-position-p="">
			<span class="mdv-d-name"></span>
		</div>
		
		<!-- Unmounted type template (enclosures / devices) -->
		<div class="mdv-unmounted-type mhf-panel" data-type="">
			<div class="mhf-p-header">
				<span class="mhf-ph-icon"></span>
				<span class="mhf-ph-title"></span>
			</div>
			<div class="mhf-p-body">
			</div>
		</div>
	</div>
</div>
EOF
		);

		// Init JS widget
		$oOutput->AddJs(
<<<EOF
// Molkobain datacenter view
// - Initializing widget
$.molkobain.{$sJSWidgetName}(
    $sJSWidgetDataJSON,
    $('.molkobain-datacenter-view-container')
);
EOF
		);

		return $oOutput;
	}


	//---------
	// Data
	//---------

	/**
	 * Returns the object data for the JS widget either as a PHP array or JSON string
	 *
	 * @param bool $bAsJSON
	 *
	 * @return array|string
	 * @throws \Exception
	 * @throws \DictExceptionMissingString
	 */
	protected function GetDataForJSWidget($bAsJSON = false)
	{
		// Note: Data could be retrieved on object instanciation along with legend data
		$aObjectData = $this->GetObjectData();

		$aData = array(
			'debug' => ConfigHelper::IsDebugEnabled(),
			'object_type' => $this->sType,
			'object_data' => $aObjectData,
			'legend' => $this->GetLegendData($aObjectData),
			'dict' => $this->GetDictEntries(),
		);

		return ($bAsJSON) ? json_encode($aData) : $aData;
	}

	/**
	 * Returns structured data about the object and its enclosures/devices
	 *
	 * @return array
	 */
	protected function GetObjectData()
	{
		$sMethodName = 'Get' . ucfirst($this->sType) . 'Data';

		return static::$sMethodName($this->oObject);
	}

	/**
	 * Returns base data of $oObject (which can be $this->oObject or one of its devices)
	 *
	 * @param \DBObject $oObject
	 *
	 * @return array
	 * @throws \CoreException
	 */
	protected function GetObjectBaseData(DBObject $oObject)
	{
		$iNbU = (empty($oObject->Get('nb_u'))) ? 1 : (int) $oObject->Get('nb_u');

		$aData = array(
			'class' => get_class($oObject),
			'id' => $oObject->GetKey(),
			'name' => $oObject->GetName(),
			'icon' => $oObject->GetIcon(false),
			'url' => $oObject->GetHyperlink(), // Note: GetHyperlink() actually return the HTML markup
			'nb_u' => $iNbU,
			'tooltip' => array(
				'content' => $this->MakeDeviceTooltipContent($oObject),
			),
		);

		return $aData;
	}

	/**
	 * @param \DBObject $oRack
	 *
	 * @return array
	 * @throws \CoreException
	 */
	protected function GetRackData(DBObject $oRack)
	{
		$aData = $this->GetObjectBaseData($oRack) + array(
				'panels' => array(
					'front' => Dict::S('Molkobain:DatacenterView:Rack:Panel:Front:Title'),
				),
				'enclosures' => array(
					'icon' => MetaModel::GetClassIcon('Enclosure', false),
					static::ENUM_ASSEMBLY_TYPE_MOUNTED => array(),
					static::ENUM_ASSEMBLY_TYPE_UNMOUNTED => array(),
				),
				'devices' => array(
					'icon' => MetaModel::GetClassIcon('DatacenterDevice', false),
					static::ENUM_ASSEMBLY_TYPE_MOUNTED => array(),
					static::ENUM_ASSEMBLY_TYPE_UNMOUNTED => array(),
				),
			);

		/** @var \DBObjectSet $oEnclosureSet */
		$oEnclosureSet = $oRack->Get('enclosure_list');
		while($oEnclosure = $oEnclosureSet->Fetch())
		{
			$aEnclosureData = $this->GetEnclosureData($oEnclosure);
			$sEnclosureAssemblyType = ($aEnclosureData['position_v'] > 0) ? static::ENUM_ASSEMBLY_TYPE_MOUNTED : static::ENUM_ASSEMBLY_TYPE_UNMOUNTED;

			$aData['enclosures'][$sEnclosureAssemblyType][] = $aEnclosureData;
		}

		$oDeviceSearch = DBObjectSearch::FromOQL('SELECT DatacenterDevice WHERE rack_id = :rack_id AND enclosure_id = 0');
		$oDeviceSet = new DBObjectSet($oDeviceSearch, array(), array('rack_id' => $oRack->GetKey()));
		while($oDevice = $oDeviceSet->Fetch())
		{
			$aDeviceData = $this->GetDeviceData($oDevice);
			$sDeviceAssemblyType = ($aDeviceData['position_v'] > 0) ? static::ENUM_ASSEMBLY_TYPE_MOUNTED : static::ENUM_ASSEMBLY_TYPE_UNMOUNTED;

			$aData['devices'][$sDeviceAssemblyType][] = $aDeviceData;
		}

		return $aData;
	}

	/**
	 * @param \DBObject $oEnclosure
	 *
	 * @return array
	 * @throws \CoreException
	 */
	protected function GetEnclosureData(DBObject $oEnclosure)
	{
		$aData = $this->GetObjectBaseData($oEnclosure) + array(
				'rack_id' => (int) $oEnclosure->Get('rack_id'),
				'position_v' => (int) $oEnclosure->Get('position_v'),
				'position_p' => 'front',
				'devices' => array(
					'icon' => MetaModel::GetClassIcon('DatacenterDevice', false),
					static::ENUM_ASSEMBLY_TYPE_MOUNTED => array(),
					static::ENUM_ASSEMBLY_TYPE_UNMOUNTED => array(),
				),
			);

		/** @var \DBObjectSet $oDeviceSet */
		$oDeviceSet = $oEnclosure->Get('device_list');
		while($oDevice = $oDeviceSet->Fetch())
		{
			$aDeviceData = $this->GetDeviceData($oDevice);
			$sDeviceAssemblyType = ($aDeviceData['position_v'] > 0) ? static::ENUM_ASSEMBLY_TYPE_MOUNTED : static::ENUM_ASSEMBLY_TYPE_UNMOUNTED;

			$aData['devices'][$sDeviceAssemblyType][] = $aDeviceData;
		}

		return $aData;
	}

	/**
	 * @param \DBObject $oDevice
	 *
	 * @return array
	 * @throws \CoreException
	 */
	protected function GetDeviceData(DBObject $oDevice)
	{
		$aData = $this->GetObjectBaseData($oDevice) + array(
				'rack_id' => (int) $oDevice->Get('rack_id'),
				'enclosure_id' => (int) $oDevice->Get('enclosure_id'),
				'position_v' => (int) $oDevice->Get('position_v'),
				'position_p' => 'front',
			);

		return $aData;
	}

	/**
	 * Returns data to build the legend (classes, counts, ...) from the $aObjectData.
	 *
	 * @param array $aObjectData *
	 * @param array $aLegendData Passed by reference
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function GetLegendData($aObjectData, &$aLegendData = array())
	{
		if(empty($aLegendData))
		{
			$aLegendData = array(
				'classes' => array(),
			);
		}

		foreach(static::EnumElementTypes() as $sElementType)
		{
			$sElementTypePlural = $sElementType . 's';
			if(!array_key_exists($sElementTypePlural, $aObjectData))
			{
				continue;
			}

			foreach(static::EnumAssemblyTypes() as $sAssemblyType)
			{
				if(!array_key_exists($sAssemblyType, $aObjectData[$sElementTypePlural]))
				{
					continue;
				}

				foreach($aObjectData[$sElementTypePlural][$sAssemblyType] as $aElement)
				{
					if(!array_key_exists($aElement['class'], $aLegendData['classes']))
					{
						$aLegendData['classes'][$aElement['class']] = array(
							'title' => MetaModel::GetName($aElement['class']),
							'count' => 0,
						);
					}
					$aLegendData['classes'][$aElement['class']]['count']++;

					foreach(static::EnumElementTypes() as $sSubElementType)
					{
						$sSubElementTypePlural = $sSubElementType . 's';
						if(array_key_exists($sSubElementTypePlural, $aElement))
						{
							$this->GetLegendData($aElement, $aLegendData);
						}
					}
				}
			}
		}

		return $aLegendData;
	}

	/**
	 * Returns the HTML content for the device's tooltip
	 *
	 * @param \DBObject $oObject
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \Exception
	 */
	protected function MakeDeviceTooltipContent(DBObject $oObject)
	{
		$sObjClass = get_class($oObject);
		$aObjMandatoryAttCodes = array('finalclass', 'org_id', 'status', 'business_criticity', 'enclosure_id');
		$aObjOptionalAttCodes = array();

		// Retrieving attributes to display in the "more" part
		$aAttCodesFromSettings = ConfigHelper::GetSetting('device_tooltip_attributes');
		foreach(MetaModel::EnumParentClasses($sObjClass, ENUM_PARENT_CLASSES_ALL, false) as $sClass)
		{
			if(is_array($aAttCodesFromSettings) && array_key_exists($sClass, $aAttCodesFromSettings) && is_array($aAttCodesFromSettings[$sClass]))
			{
				$aObjOptionalAttCodes = $aAttCodesFromSettings[$sClass];
				break;
			}
		}

		$aObjAttCodes = array(
			'base-info' => $aObjMandatoryAttCodes,
			'more-info' => $aObjOptionalAttCodes,
		);

		// Building the HTML markup
		// - Base info
		$sClassImage = $oObject->GetIcon();
		$sObjName = $oObject->GetName();
		// - Attributes
		$sAttributesHTML = '';
		foreach($aObjAttCodes as $sAttCategory => $aAttCodes)
		{
			if(count($aAttCodes) > 0)
			{
				$sAttributesHTML .= '<div class="mdv-dt-list-wrapper mdv-dt-' . $sAttCategory . '">';
				$sAttributesHTML .= '<fieldset><legend>' . Dict::S('Molkobain:DatacenterView:Element:Tooltip:Fieldset:' . $sAttCategory) . '</legend>';
				$sAttributesHTML .= '<ul>';
				foreach($aAttCodes as $sAttCode)
				{
					if(!MetaModel::IsValidAttCode($sObjClass, $sAttCode))
					{
						continue;
					}

					$sAttLabel = MetaModel::GetLabel($sObjClass, $sAttCode);
					/** @var \AttributeDefinition $oAttDef */
					$oAttDef = MetaModel::GetAttributeDef($sObjClass, $sAttCode);
					if($oAttDef instanceof AttributeExternalKey)
					{
						$sAttValue = htmlentities($oObject->Get($sAttCode . '_friendlyname'), ENT_QUOTES, 'UTF-8');
					}
					else
					{
						$sAttValue = htmlentities($oAttDef->GetValueLabel($oObject->Get($sAttCode)), ENT_QUOTES, 'UTF-8');
					}
					$sAttributesHTML .= '<li><span class="mdv-dtl-label">' . $sAttLabel . '</span><span class="mdv-dtl-value">' . $sAttValue . '</span></li>';
				}
				$sAttributesHTML .= '</ul>';
				$sAttributesHTML .= '</fieldset>';
				$sAttributesHTML .= '</div>';
			}
		}

		$sHTML = <<<EOF
	<div class="mdv-dt-header">
		<span class="mdv-dth-icon">{$sClassImage}</span>
		<span class="mdv-dth-name">{$sObjName}</span>
	</div>
	{$sAttributesHTML}
EOF;

		return $sHTML;
	}

	/**
	 * Returns dictionary entries used by the JS widget
	 *
	 * @return array
	 * @throws \DictExceptionMissingString
	 */
	protected function GetDictEntries()
	{
		return array(
			'Molkobain:DatacenterView:Unmounted:Enclosures:Title' => Dict::S('Molkobain:DatacenterView:Unmounted:Enclosures:Title'),
			'Molkobain:DatacenterView:Unmounted:Enclosures:Title+' => Dict::S('Molkobain:DatacenterView:Unmounted:Enclosures:Title+'),
			'Molkobain:DatacenterView:Unmounted:Devices:Title' => Dict::S('Molkobain:DatacenterView:Unmounted:Devices:Title'),
			'Molkobain:DatacenterView:Unmounted:Devices:Title+' => Dict::S('Molkobain:DatacenterView:Unmounted:Devices:Title+'),
		);
	}


	//-----------------
	// Static methods
	//-----------------


	/**
	 * Note: Using a static method instead of a static array as it is not possible in PHP 5.6
	 *
	 * @return array
	 */
	public static function EnumElementTypes()
	{
		return array(
			static::ENUM_ELEMENT_TYPE_RACK,
			static::ENUM_ELEMENT_TYPE_ENCLOSURE,
			static::ENUM_ELEMENT_TYPE_DEVICE,
		);
	}

	/**
	 * Note: Using a static method instead of a static array as it is not possible in PHP 5.6
	 *
	 * @return array
	 */
	public static function EnumAssemblyTypes()
	{
		return array(
			static::ENUM_ASSEMBLY_TYPE_MOUNTED,
			static::ENUM_ASSEMBLY_TYPE_UNMOUNTED,
		);
	}

	/**
	 * Returns if the $oObject is a rack|enclosure|device (see ENUM_ELEMENT_TYPE_XXX constants)
	 *
	 * @param \DBObject $oObject
	 *
	 * @return string
	 */
	public static function FindObjectType(DBObject $oObject)
	{
		$sObjClass = get_class($oObject);
		switch($sObjClass)
		{
			case 'Rack':
				$sObjType = 'rack';
				break;
			case 'Enclosure':
				$sObjType = 'enclosure';
				break;
			default:
				$sObjType = 'device';
				break;
		}

		return $sObjType;
	}
}
