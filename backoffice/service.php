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
	 * @param string $sectionName
	 * @param Widget|string $widget
	 * @throws Exception
	 */
	public function registerWidget($sectionName, $widget)
	{
		$sectionName = strtolower($sectionName);
		$section = $this->getSection($sectionName);

		if (isset($section['hasWidget']) && $section['hasWidget'] === false) {
			throw new Exception($sectionName, Exception::WIDGET_NOT_ALLOWED);
		}

		$this->widgets[$sectionName][] = $widget;
	}

	/**
	 * @param string $widgetClassName
	 * @return Widget
	 * @throws Exception
	 */
	private function generateWidget($widgetClassName)
	{
		$widget = $this->getApp()->getFactory()->get($widgetClassName);

		if (!$widget instanceof Widget) {
			throw new Exception($widgetClassName, Exception::INVALID_WIDGET_OBJECT);
		}

		return $widget;
	}

	/**
	 * @param string $sectionName
	 */
	private function generateSectionWidgets($sectionName)
	{
		// Only if not done
		if (isset($this->widgetsInstance[$sectionName])) {
			return;
		}
		foreach ($this->widgets[$sectionName] as $oneWidget) {
			$widget = $this->generateWidget($oneWidget);
			$this->widgetsInstance[$sectionName][$widget->getName()] = $widget;
		}
	}

	/**
	 * @return Widget[]
	 */
	public function getAllWidgets()
	{
		if (count($this->widgetsInstance, COUNT_RECURSIVE) != count($this->widgets, COUNT_RECURSIVE)) {
			foreach ($this->widgets as $sectionName => $oneSectionWidgets) {
				$this->generateSectionWidgets($sectionName);
			}
		}

		return $this->widgetsInstance;
	}

	/**
	 * @param $sectionName
	 * @return Widget[]
	 * @throws Exception
	 */
	public function getSectionWidgets($sectionName)
	{
		$section = $this->getSection($sectionName);

		if (isset($section['hasWidget']) && $section['hasWidget'] === false) {
			throw new Exception($sectionName, Exception::WIDGET_NOT_ALLOWED);
		}

		if (!isset($this->widgets[$sectionName])) {
			return array();
		}

		if (!isset($this->widgetsInstance[$sectionName]) || count($this->widgetsInstance[$sectionName]) != count(
				$this->widgets[$sectionName]
			)
		) {
			$this->generateSectionWidgets($sectionName);
		}

		return $this->widgetsInstance[$sectionName];
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