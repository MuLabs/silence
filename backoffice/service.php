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

		if ($widget instanceof Widget) {
			$this->widgets[$sectionName][] = $widget;
		}
	}

	/**
	 * @return Widget[]
	 */
	public function getAllWidgets()
	{
		if (count($this->widgetsInstance, COUNT_RECURSIVE) != count($this->widgets, COUNT_RECURSIVE)) {
			foreach ($this->widgets as $sectionName => $oneSectionWidgets) {
				foreach ($oneSectionWidgets as $key => $oneWidget) {
					if (!isset($this->widgetsInstance[$sectionName][$key])) {
						$this->widgetsInstance[$sectionName][$key] = $this->getApp()->getFactory()->get($oneWidget);
					}
				}
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

		if (!isset($this->widgets[$sectionName]) && !isset($this->widgetsInstance[$sectionName])) {
			return array();
		}

		if (count($this->widgetsInstance[$sectionName]) != count($this->widgets[$sectionName])) {
			foreach ($this->widgets[$sectionName] as $key => $oneWidget) {
				if (!isset($this->widgetsInstance[$sectionName][$key])) {
					$this->widgetsInstance[$key] = $this->getApp()->getFactory()->get($oneWidget);
				}
			}
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