<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 14:27:02
         compiled from "/var/www/TeamTime/themes/anis/templates/grille2.tpl" */ ?>
<?php /*%%SmartyHeaderCode:10445111375188f3162c2bf0-19415521%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bf34ea511b738af483042f1cc36d628f7071bdbd' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/grille2.tpl',
      1 => 1366901619,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '10445111375188f3162c2bf0-19415521',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'grille' => 0,
    'lineNb' => 0,
    'lines' => 0,
    'value' => 0,
    'previousCycle' => 0,
    'image' => 0,
    'presentCycle' => 0,
    'nextCycle' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188f31638ec81_26838784',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188f31638ec81_26838784')) {function content_5188f31638ec81_26838784($_smarty_tpl) {?>


<div id="tgrille">
	<table id="grille">
<?php  $_smarty_tpl->tpl_vars["lines"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["lines"]->_loop = false;
 $_smarty_tpl->tpl_vars["lineNb"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['grille']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["lines"]->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars["lines"]->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars["lines"]->key => $_smarty_tpl->tpl_vars["lines"]->value){
$_smarty_tpl->tpl_vars["lines"]->_loop = true;
 $_smarty_tpl->tpl_vars["lineNb"]->value = $_smarty_tpl->tpl_vars["lines"]->key;
 $_smarty_tpl->tpl_vars["lines"]->iteration++;
 $_smarty_tpl->tpl_vars["lines"]->last = $_smarty_tpl->tpl_vars["lines"]->iteration === $_smarty_tpl->tpl_vars["lines"]->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["lineBoucle"]['last'] = $_smarty_tpl->tpl_vars["lines"]->last;
?>
	<?php if ($_smarty_tpl->tpl_vars['lineNb']->value==0){?><thead><?php }elseif($_smarty_tpl->tpl_vars['lineNb']->value==2){?></thead>
	<tbody><?php }?>
	<tr id="line<?php echo $_smarty_tpl->tpl_vars['lineNb']->value;?>
">
<?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['value']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['lines']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value){
$_smarty_tpl->tpl_vars['value']->_loop = true;
?>
	<?php if ($_smarty_tpl->tpl_vars['lineNb']->value<2){?><th<?php }else{ ?><td<?php }?><?php if ($_smarty_tpl->tpl_vars['value']->value['id']){?> id="<?php echo $_smarty_tpl->tpl_vars['value']->value['id'];?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['value']->value['classe']){?> class="<?php echo $_smarty_tpl->tpl_vars['value']->value['classe'];?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['value']->value['title']){?> title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['value']->value['title'], ENT_QUOTES, 'UTF-8', true);?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['value']->value['colspan']){?> colspan="<?php echo $_smarty_tpl->tpl_vars['value']->value['colspan'];?>
"<?php }?>><?php if ($_smarty_tpl->tpl_vars['value']->value['navigateur']){?>
	<div class="nav-prev"><a href="?dateDebut=<?php echo $_smarty_tpl->tpl_vars['previousCycle']->value;?>
" title="Reculer d'un cycle"><img src="<?php echo $_smarty_tpl->tpl_vars['image']->value;?>
" class="nav-prev" alt="&lt;" /></a></div>
	<div class="nav-present"><a href="?dateDebut=<?php echo $_smarty_tpl->tpl_vars['presentCycle']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['nom'];?>
</a></div>
	<div class="nav-next"><a href="?dateDebut=<?php echo $_smarty_tpl->tpl_vars['nextCycle']->value;?>
" title="Avancer d'un cycle"><img src="<?php echo $_smarty_tpl->tpl_vars['image']->value;?>
" class="nav-next" alt="&lt;" /></a></div><?php }elseif($_smarty_tpl->tpl_vars['value']->value['vacation']){?>
	<div class="<?php echo $_smarty_tpl->tpl_vars['value']->value['vacances'];?>
"></div>
	<div class="<?php echo $_smarty_tpl->tpl_vars['value']->value['periodeCharge'];?>
"></div>
	<div class="<?php if (!$_smarty_tpl->tpl_vars['value']->value['briefing']){?>no<?php }?>brief"<?php if ($_smarty_tpl->tpl_vars['value']->value['briefing']){?> title="<?php echo $_smarty_tpl->tpl_vars['value']->value['briefing'];?>
"<?php }?>></div>
	<div class="dateGrille"><?php echo $_smarty_tpl->tpl_vars['value']->value['jds'];?>
</div>
	<div class="dateGrille"><?php echo $_smarty_tpl->tpl_vars['value']->value['jdm'];?>
</div>
	<div class="shift"><?php echo $_smarty_tpl->tpl_vars['value']->value['vacation'];?>
</div><?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['value']->value['nom'];?>
<?php }?>
<?php if ($_smarty_tpl->tpl_vars['lineNb']->value<2){?>
	</th><?php }else{ ?></td>
	<?php }?><?php } ?></tr>
<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['lineBoucle']['last']){?></tbody><?php }?><?php } ?>
	</table>
</div>
 
		<div id="sandbox"></div>

		<form id="fFormRemplacement" method="post" action="set_rempla.php">
		<div id="dFormRemplacement">
				<input type="hidden" name="uid" id="remplaUid" />
				<input type="hidden" name="Year" id="remplaYear" />
				<input type="hidden" name="Month" id="remplaMonth" />
				<input type="hidden" name="Day" id="remplaDay" />
			<table>
			<tr>
			<td>
				<label for="remplaNom">Nom&nbsp;:</label>
			</td><td>
				<input name="nom" id="remplaNom" type="text" />
			</td></tr><tr><td>
				<label for="remplaPhone">Téléphone&nbsp;:</label>
			</td><td>
				<input name="phone" id="remplaPhone" type="text" />
			</td></tr><tr><td>
				<label for="remplaEmail">remplaEmail&nbsp;</label>
			</td><td>
				<input name="email" id="remplaEmail" type="text" />
			</td></tr><tr><td>
				<label for="remplaAlert">Envoyer un mail&nbsp;:</label>
			</td><td>
				<input name="alert" id="remplaAlert" type="checkbox" />
			</td></tr><tr><td>
				<input type="reset" value="Effacer" />
			</td><td>
				<input type="submit" />
			</td></tr>
			</table>
		</div>
		</form>

	
<?php }} ?>