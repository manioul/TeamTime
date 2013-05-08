<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:15:30
         compiled from "/var/www/TeamTime/themes/anis/templates/timeInfo.tpl" */ ?>
<?php /*%%SmartyHeaderCode:19798332335188fe72d9f0c4-34974548%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c7df4993bb7762b1996b15efe76e00b3f2f2582e' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/timeInfo.tpl',
      1 => 1367932526,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19798332335188fe72d9f0c4-34974548',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'constructTime' => 0,
    'debugTimes' => 0,
    'key' => 0,
    'place' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188fe72e55872_70599891',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188fe72e55872_70599891')) {function content_5188fe72e55872_70599891($_smarty_tpl) {?><?php if (!is_callable('smarty_function_math')) include '/usr/share/php/smarty3/plugins/function.math.php';
?>
<div id="timeInfo">
La cr&eacute;ation de cette page a n&eacute;cessit&eacute; <span class='temps'><?php echo $_smarty_tpl->tpl_vars['constructTime']->value;?>
</span> secondes.<br />
<table>
<thead>
<tr>
<th>Nom</th><th>instantann&eacute;</th><th>cumul&eacute;</th><th>fonction chrono</th><th>% temps total</th>
</tr>
</thead>
<tbody>
<?php  $_smarty_tpl->tpl_vars['place'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['place']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['debugTimes']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['place']->key => $_smarty_tpl->tpl_vars['place']->value){
$_smarty_tpl->tpl_vars['place']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['place']->key;
?>
<tr>
<td><?php echo $_smarty_tpl->tpl_vars['key']->value;?>
</td><td><span class='temps'><?php echo $_smarty_tpl->tpl_vars['place']->value['instant'];?>
</span></td><td><span class='temps'><?php echo $_smarty_tpl->tpl_vars['place']->value['cumule'];?>
</span></td><td><?php echo $_smarty_tpl->tpl_vars['place']->value['chrono'];?>
 (chrono fonct.)</td><td class="temps"><?php echo smarty_function_math(array('equation'=>"x*100/y",'x'=>$_smarty_tpl->tpl_vars['place']->value['instant'],'y'=>$_smarty_tpl->tpl_vars['constructTime']->value,'format'=>"%.2f%%"),$_smarty_tpl);?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php }} ?>