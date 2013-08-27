<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {scriptholder} block plugin
 *
 * Type:     block<br>
 * Name:     scriptholder<br>
 * Purpose:  scriptholder

 * @author yancan <yancan at aspire-tech dot com>
 * @param array
 * @param Smarty
 */
function smarty_block_scriptholder($params, $content, &$smarty, $open)
{
    if(!$open) {
        if(isset($params['output']) and $params['output']) {
            return MI_Template::getScriptHolder($params);
        }

        MI_Template::scriptHolder($params, $content);
    }
}

/* vim: set expandtab: */

