<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class GridHtml extends ModuleGridEngine
{
	private $_values;
	private static $_columns;

	function __construct($type = null)
	{
		if ($type != null)
			parent::__construct($type);
		else
		{
			$this->name = 'gridhtml';
			$this->tab = 'administration';
			$this->version = 1.0;
			
			Module::__construct();
			
			$this->displayName = $this->l('Simple HTML table display');
			$this->description = '';
		}
	}
	
	function install()
	{
		return (parent::install() AND $this->registerHook('GridEngine'));
	}
	
	public static function hookGridEngine($params, $grider)
	{
		self::$_columns = $params['columns'];
		if (!isset($params['emptyMsg']))
			$params['emptyMsg'] = 'Empty';

		$html = '<div style="width:'.$params['width'].'px;height:'.$params['height'].'px;overflow:scroll">
			<table class="table" cellpadding="0" cellspacing="0" id="grid_1"><thead><tr>';
		foreach ($params['columns'] as $column)
			$html .= '<th style="width:'.$column['width'].'px;cursor:pointer">
						'.$column['header'].'<br />
						<a href="javascript:getGridData(\''.$grider.'&sort='.$column['dataIndex'].'&dir=ASC\');">
							<img src="../img/admin/up.gif" />
						</a>
						<a href="javascript:getGridData(\''.$grider.'&sort='.$column['dataIndex'].'&dir=DESC\');">
							<img src="../img/admin/down.gif" />
						</a>
					</th>';
		$html .= '</tr></thead>
				<tbody></tbody>
				<tfoot><tr><th colspan="'.count($params['columns']).'">'.$params['pagingMessage'].'</th></tr></tfoot>
			</table>
		</div>
		<script type="text/javascript">
			function getGridData(url)
			{
				$("#grid_1 tbody").html("<tr><td style=\"text-align:center\" colspan=\"" + '.count($params['columns']).' + "\"><img src=\"../img/loadingAnimation.gif\" /></td></tr>");
				$.get(url, "", function(json) {
					$("#grid_1 tbody").html("");
					var array = $.parseJSON(json);
					$("#grid_1 tfoot tr th").html($("#grid_1 tfoot tr th").html().replace("{0}", array["from"]));
					$("#grid_1 tfoot tr th").html($("#grid_1 tfoot tr th").html().replace("{1}", array["to"]));
					$("#grid_1 tfoot tr th").html($("#grid_1 tfoot tr th").html().replace("{2}", array["total"]));
					var values = array["values"];
					if (values.length > 0)
						$.each(values, function(index, row){
							var newLine = "<tr>";';
			foreach ($params['columns'] as $column)
				$html .= '	newLine += "<td'.(isset($column['align']) ? ' style=\"text-align:'.$column['align'].'\"' : '').'>" + row["'.$column['dataIndex'].'"] + "</td>";';
			$html .= '		$("#grid_1 tbody").append(newLine);
						});
					else
						$("#grid_1 tbody").append("<tr><td style=\"text-align:center\" colspan=\"" + '.count($params['columns']).' + "\">'.$params['emptyMsg'].'</td></tr>");
				});
			}
			$(document).ready(function(){getGridData("'.$grider.'&sort='.urlencode($params['defaultSortColumn']).'&dir='.urlencode($params['defaultSortDirection']).'");});
		</script>';
		return $html;
	}
	
	public function setColumnsInfos(&$infos)
	{
	}
	
	public function setValues($values)
	{
		$this->_values = $values;
	}
	
	public function setTitle($title)
	{
		$this->_title = $title;
	}
	
	public function setSize($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}
	
	public function setTotalCount($totalCount)
	{
		$this->_totalCount = $totalCount;
	}
	
	public function setLimit($start, $limit)
	{
		$this->_start = (int)$start;
		$this->_limit = (int)$limit;
	}
	
	public function render()
	{
		echo json_encode(array(
			'total' => $this->_totalCount,
			'from' => $this->_start + 1,
			'to' => min($this->_start + $this->_limit, $this->_totalCount),
			'values' => $this->_values
		));
	}	
}

