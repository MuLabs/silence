<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	private $sections = array();
	private $widgets = array();
	private $widgetsInstance = array();
	private $actionLogger;

	/**
	 * @param array
	 */
	public function setSections(array $sections)
	{
		$this->sections = $sections;
	}

	/**
	 * @return array
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * @param $sectionName
	 * @return string
	 * @throws Exception
	 */
	public function getSection($sectionName)
	{
		if (!isset($this->sections[$sectionName])) {
			throw new Exception($sectionName, Exception::SECTION_NOT_FOUND);
		}

		return $this->sections[$sectionName];
	}

	/**
	 * @param string $widgetName
	 * @param Widget|string $widget
	 */
	public function registerWidget($widgetName, $widget)
	{
		$widgetName = strtolower($widgetName);
		if ($widget instanceof Widget) {
			$this->widgetsInstance[$widgetName] = $widget;
			$this->widgets[$widgetName] = get_class($widget);
		} else {
			$this->widgets[$widgetName] = $widget;
		}
	}

	/**
	 * @return Widget[]
	 */
	public function getWidgets()
	{
		if (count($this->widgetsInstance) != count($this->widgets)) {
			foreach ($this->widgets as $oneWidget) {
				if (!isset($this->widgetsInstance[$oneWidget])) {
					$this->widgetsInstance[$oneWidget] = $this->getApp()->getFactory()->get($oneWidget);
				}
			}
		}

		return $this->widgetsInstance;
	}

	/**
	 * @param $widgetName
	 * @return string
	 * @throws Exception
	 */
	public function getWidget($widgetName)
	{
		if (!isset($this->widgets[$widgetName]) && !isset($this->widgetsInstance[$widgetName])) {
			throw new Exception($widgetName, Exception::WIDGET_NOT_FOUND);
		}

		if (!$this->widgetsInstance[$widgetName]) {
			$this->widgetsInstance[$widgetName] = $this->getApp()->getFactory()->get($widgetName);
		}

		return $this->widgetsInstance[$widgetName];
	}

	/**
	 * @param ActionLogger $actionLogger
	 */
	public function setActionLogger(ActionLogger $actionLogger)
	{
		$this->actionLogger = $actionLogger;
	}

	/**
	 * @return ActionLogger
	 */
	public function getActionLogger()
	{
		return $this->actionLogger;
	}
}