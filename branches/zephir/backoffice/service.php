<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $sections = array();
	protected $widgets 	= array();
	protected $widgetsInstance = array();
	protected $actionLogger;

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
	 * @param $name
	 * @return bool|array
	 */
	public function getSubSection($name)
	{
		foreach ($this->sections as $section) {
			if (!isset($section['sub']) || !isset($section['sub'][$name])) {
				continue;
			}

			return $section['sub'][$name];
		}

		return false;
	}

	/**
	 * @param string $section
	 * @return array
	 * @throws Exception
	 * @throws Kernel\Route\Exception
	 */
	public function generateBreadcrumbs($section = '')
	{
		$section = (isset($this->sections[$section])) ? $this->sections[$section] : $this->getSubSection($section);
		if (empty($section)) {
			return array();
		}

		$app = $this->getApp();
		$routeManager = $app->getRouteManager();

		// Get current section breadcrumb:
		$breadcrumb = (isset($section['breadcrumb'])) ? $section['breadcrumb'] : false;
		if (!$breadcrumb) {
			return array();
		}

		// Get current section url:
		$menu = (isset($section['menu'])) ? $section['menu'] : false;
		if ($menu) {
            $param = (isset($menu['param'])) ? $menu['param'] : array();
			$breadcrumb['url'] = $routeManager->getUrl($menu['route'], $param);
		}

		// Generate breadcrumbs hierarchy:
		$result[] = $breadcrumb;
		$parent   = (isset($breadcrumb['parent'])) ? $breadcrumb['parent'] : false;
		while (!empty($parent)) {
			$section = (isset($this->sections[$parent])) ? $this->sections[$parent] : array();
			if (!isset($section['breadcrumb']) || !isset($section['menu'])) {
				break;
			}

			$menu 		= $section['menu'];
			$breadcrumb = $section['breadcrumb'];
			if (isset($menu['route'])) {
                $param = (isset($menu['param'])) ? $menu['param'] : array();
				$breadcrumb['url'] = $routeManager->getUrl($menu['route'], $param);
			}
			array_unshift($result, $breadcrumb);

			// Get next parent:
			$parent = (isset($breadcrumb['parent'])) ? $breadcrumb['parent'] : false;
		}

		// Return the list:
		return $result;
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
		foreach ($this->widgets[$sectionName] as $oneWidget) {
			$widget = $this->generateWidget($oneWidget);
			$this->widgetsInstance[$sectionName][$widget->getName()] = $widget;
		}
	}

	/**
	 * @return array
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

		if (!isset($this->widgetsInstance[$sectionName]) ||
            count($this->widgetsInstance[$sectionName]) != count($this->widgets[$sectionName])) {
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