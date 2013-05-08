{* Smarty *}
		<ul class="lien"><li><h1>Support</h1>
			<div id="dSupport" class="desc boite">
				<ul class="sousmenu">
				{foreach from=$content key=k item=v}
					<li><a href="{$v}">{$k}</a></li>
				{/foreach}
				</ul>
			</div>
			</li>
		</ul>
