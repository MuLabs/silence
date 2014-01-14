<li{if isset($section['class'])} class="{$section['class']}"{/if}>
	<a{if isset($section['route'])} href="{$this->getExtension('Core')->getUrl($section['route'], $section['param'])}"{/if}>
		<span>{$section['title']}</span>
	</a>
	{if isset($section['sub']) && is_array($section['sub']) && count($section['sub'])}
		<ul>
			<li>
				{foreach from=$section['sub'] item=subSection}
					{include 'oneSection.tpl' section=$subSection}
				{/foreach}
			</li>
		</ul>
	{/if}
	{if isset($section['add'])}
	<a class="i_plus menu_ext"
	   href="{$this->getExtension('Core')->getUrl($section['route'], array_merge($section['add'], $section['param']))}"></a>{/if}
</li>