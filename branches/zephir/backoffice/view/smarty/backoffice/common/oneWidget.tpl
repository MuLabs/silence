{assign var=options value=$widget->getDefaultOptions()}
{assign var=name    value=$widget->getName()}
{assign var=title   value=$widget->getTitle()}

<div class="widget">
	<h3 class="handle">{$widget->getTitle()}</h3>

	<div>
		{if isset($widgetContent)}{$widgetContent}{/if}
	</div>
</div>