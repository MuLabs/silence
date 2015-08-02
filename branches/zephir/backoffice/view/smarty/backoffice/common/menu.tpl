<nav id="gl_nav">
	<ul id="nav">
		{foreach from=$backoffice->getSections() item=oneSection}
			{include 'backoffice/common/oneSection.tpl' section=$oneSection}
		{/foreach}
	</ul>
</nav>