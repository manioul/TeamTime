<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/download.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18909364955188cf14a53fe0-59105303%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1d8838948780076aea478b1ed33ce319fa41ce0f' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/download.tpl',
      1 => 1366968022,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '18909364955188cf14a53fe0-59105303',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf14ab21a7_31456309',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf14ab21a7_31456309')) {function content_5188cf14ab21a7_31456309($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_radios')) include '/usr/share/php/smarty3/plugins/function.html_radios.php';
?>
		<ul class="lien"><li><div><h1>Télécharger</h1>
			<form id="fDl" name="fDl" method="post" action="dl.php">
				<div id="dDl" class="desc boite">
				<?php echo smarty_function_html_radios(array('name'=>'v','values'=>$_smarty_tpl->tpl_vars['content']->value['val'],'output'=>$_smarty_tpl->tpl_vars['content']->value['nam'],'selected'=>$_smarty_tpl->tpl_vars['content']->value['sel'],'separator'=>'<br />'),$_smarty_tpl);?>

				<input type="submit" class="button" value="charger" />
				</div>
			</form></div>
			</li>
		</ul>
	
<?php }} ?>