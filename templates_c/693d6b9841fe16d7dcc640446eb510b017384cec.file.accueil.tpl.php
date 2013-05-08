<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/accueil.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1653868995188cf14a0b213-97707255%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '693d6b9841fe16d7dcc640446eb510b017384cec' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/accueil.tpl',
      1 => 1366967994,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1653868995188cf14a0b213-97707255',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf14a22fa8_85375424',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf14a22fa8_85375424')) {function content_5188cf14a22fa8_85375424($_smarty_tpl) {?>
		<ul class="lien"><li><h1><a href="index.php">Accueil</a></h1>
			<?php if ($_smarty_tpl->tpl_vars['content']->value['accueil']!=''){?><div id="dAccueil" class="desc boite">
				<?php echo $_smarty_tpl->tpl_vars['content']->value['accueil'];?>

			</div><?php }?>
			</li>
		</ul>
<?php }} ?>