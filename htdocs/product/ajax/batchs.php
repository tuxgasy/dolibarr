<?php
/* Copyright (C) 2019 Garcia MICHEL <garcia@soamichel.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file htdocs/product/ajax/batchs.php
 * \brief File to return Ajax response on batch list request
 */
if (! defined('NOTOKENRENEWAL'))
	define('NOTOKENRENEWAL', 1); // Disables token renewal
if (! defined('NOREQUIREMENU'))
	define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))
	define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))
	define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))
	define('NOREQUIRESOC', '1');
if (! defined('NOCSRFCHECK'))
	define('NOCSRFCHECK', '1');
if (empty($_GET ['keysearch']) && ! defined('NOREQUIREHTML'))
	define('NOREQUIREHTML', '1');

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

$productid = GETPOST('productid', 'int');
$batch = GETPOST('batch', 'alpha');

top_httphead();

if(empty($productid)){
  print json_encode(array());
  exit;
}

$product = new Product($db);
$res = $product->fetch($productid);
if($res <= 0 or !$product->hasbatch()){
  print json_encode(array());
  exit;
}

$sql = "SELECT pb.batch, pb.qty, e.label FROM ".MAIN_DB_PREFIX."product_batch AS pb";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock AS ps ON ps.rowid = pb.fk_product_stock";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."entrepot AS e ON e.rowid = ps.fk_entrepot";
$sql.= " WHERE ps.fk_product = ".$product->id;
if(!empty($batch)){
  $sql.= " ".natural_search('pb.batch', $batch);
}

$resql = $db->query($sql);
if(!$resql){
  print json_encode(array());
  exit;
}

$arrayresult = array();
while($obj = $db->fetch_object($resql)){
  $arrayresult[] = array(
    'value' => $obj->batch,
    'label' => $obj->batch.' - '.$obj->label.' - '.$langs->transnoentities('Qty').' : '.$obj->qty
  );
}

print json_encode($arrayresult);
