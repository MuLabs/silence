{if isset($section['menu']) && is_array($section['menu'])}
	<li{if isset($section['menu']['class'])} class="{$section['menu']['class']}"{/if}>
		<a{if isset($section['menu']['route'])} href="{$this->getExtension('Core')->getUrl($section['menu']['route'], $section['menu']['param'])}"{/if}>
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
		{if isset($section['menu']['add'])}
		<a class="i_plus menu_ext"
		   href="{$this->getExtension('Core')->getUrl($section['menu']['route'], array_merge($section['menu']['add'], $section['menu']['param']))}"></a>{/if}
	</li>
{/if}