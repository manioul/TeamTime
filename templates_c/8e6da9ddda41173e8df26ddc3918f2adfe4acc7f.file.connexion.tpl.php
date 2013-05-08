<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/connexion.tpl" */ ?>
<?php /*%%SmartyHeaderCode:11464402835188cf14a25484-81644559%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8e6da9ddda41173e8df26ddc3918f2adfe4acc7f' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/connexion.tpl',
      1 => 1366968008,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11464402835188cf14a25484-81644559',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf14a30f88_37491746',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf14a30f88_37491746')) {function content_5188cf14a30f88_37491746($_smarty_tpl) {?>
		<ul class="lien"><li><div><h1>Connexion</h1><br />
			<form id="fConnexion" method="post" action="logon.php">
				<div id="dConnexion" class="boite">
					<input class="" type="text" id="login" name="login" />
					<input class="" type="password" id="pwd" name="pwd" />
					<input class="button" type="submit" id="connex" name="connex" value="Connexion" />
					<input type="hidden" name="salt" value="<?php echo $_smarty_tpl->tpl_vars['content']->value['salt'];?>
" />
				
				</div>
			</form></div>
		</li></ul>
<?php }} ?>