<?php
/**
 * The MIT License (MIT)
 *
 * Webzash - Easy to use web based double entry accounting software
 *
 * Copyright (c) 2014 Prashant Shah <pshah.mumbai@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

function account_st_short($account, $c = 0, $THIS, $dc_type)
{
	$counter = $c;
	if ($account->id != 0)
	{
		if ($dc_type == 'D' && $account->cl_total_dc == 'C' && calculate($account->cl_total, 0, '!=')) {
			echo '<tr class="tr-group dc-error">';
		} else if ($dc_type == 'C' && $account->cl_total_dc == 'D' && calculate($account->cl_total, 0, '!=')) {
			echo '<tr class="tr-group dc-error">';
		} else {
			echo '<tr class="tr-group">';
		}

		echo '<td class="td-group">';
		echo print_space($counter);
		echo "&nbsp;" . $account->name;
		echo '</td>';

		echo '<td class="text-right">';
		echo toCurrency($account->cl_total_dc, $account->cl_total);
		echo print_space($counter);
		echo '</td>';

		echo '</tr>';
	}
	foreach ($account->children_groups as $id => $data)
	{
		$counter++;
		account_st_short($data, $counter, $THIS, $dc_type);
		$counter--;
	}
	if (count($account->children_ledgers) > 0)
	{
		$counter++;
		foreach ($account->children_ledgers as $id => $data)
		{
			if ($dc_type == 'D' && $data['cl_total_dc'] == 'C' && calculate($data['cl_total'], 0, '!=')) {
				echo '<tr class="tr-ledger dc-error">';
			} else if ($dc_type == 'C' && $data['cl_total_dc'] == 'D' && calculate($data['cl_total'], 0, '!=')) {
				echo '<tr class="tr-ledger dc-error">';
			} else {
				echo '<tr class="tr-ledger">';
			}

			echo '<td class="td-ledger">';
			echo print_space($counter);
			echo $THIS->Html->link($data['name'], array('controller' => 'reports', 'action' => 'ledgerstatement', 'ledgerid' => $data['id']));
			echo '</td>';

			echo '<td class="text-right">';
			echo toCurrency($data['cl_total_dc'], $data['cl_total']);
			echo print_space($counter);
			echo '</td>';

			echo '</tr>';
		}
	$counter--;
	}
}

function print_space($count)
{
	$html = '';
	for ($i = 1; $i <= $count; $i++) {
		$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	return $html;
}

$gross_total = 0;
$positive_gross_pl = 0;
$net_total = 0;
$positive_net_pl = 0;

?>

<script type="text/javascript">
$(document).ready(function() {
	$('.show-tooltip').tooltip({trigger: 'manual'}).tooltip('show');
});
</script>

<table>

	<!-- Gross Profit and Loss -->
	<tr>
		<td>
			<table>
				<tr>
					<th>Gross Expenses</th>
					<th class="text-right">(Dr) Amount</th>
				</tr>
				<?php
					foreach ($pandl['gross_expense_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'D');
					}
				?>
			</table>
		</td>

		<td>
			<table>
				<tr>
					<th>Gross Incomes</th>
					<th class="text-right">(Cr) Amount</th>
				</tr>
				<?php
					foreach ($pandl['gross_income_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'C');
					}
				?>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table>
				<?php $gross_total = $pandl['gross_expense_total']; ?>
				<?php if (calculate($pandl['gross_expense_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . 'Total Gross Expenses' . '</td>';
					echo '<td class="text-right">' . toCurrency('D', $pandl['gross_expense_total']) . '</td>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . 'Total Gross Expenses' . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting Dr Balance">' . toCurrency('D', $pandl['gross_expense_total']) . '</td>';
				}
				?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['gross_pl'], 0, '>=')) {
						echo '<td>' . 'Gross Profit C/O' . '</td>';
						echo '<td class="text-right">' . toCurrency('', $pandl['gross_pl']) . '</td>';
						$gross_total = calculate($gross_total, $pandl['gross_pl'], '+');
					} else {
						echo '<td>-</td>';
						echo '<td>-</td>';
					}
					?>
				</tr>
				<tr>
					<td>Total</td>
					<td class="text-right"><?php echo toCurrency('', $gross_total); ?></td>
				</tr>
			</table>
		</td>

		<td>
			<table>
				<?php $gross_total = $pandl['gross_income_total']; ?>
				<?php if (calculate($pandl['gross_income_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . 'Total Gross Incomes' . '</td>';
					echo '<td class="text-right">' . toCurrency('C', $pandl['gross_income_total']) . '</td>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . 'Total Gross Incomes' . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting Cr Balance">' . toCurrency('C', $pandl['gross_income_total']) . '</td>';
				}
				?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['gross_pl'], 0, '>=')) {
						echo '<td>-</td>';
						echo '<td>-</td>';
					} else {
						echo '<td>' . 'Gross Loss C/O' . '</td>';
						$positive_gross_pl = calculate($pandl['gross_pl'], 0, 'n');
						echo '<td class="text-right">' . toCurrency('', $positive_gross_pl) . '</td>';
						$gross_total = calculate($gross_total, $positive_gross_pl, '+');
					}
					?>
				</tr>
				<tr>
					<td>Total</td>
					<td class="text-right"><?php echo toCurrency('', $gross_total); ?></td>
				</tr>
			</table>
		</td>
	</tr>

	<!-- Net Profit and Loss -->
	<tr>
		<td>
			<table>
				<tr>
					<th>Expenses</th>
					<th class="text-right">(Dr) Amount</th>
				</tr>
				<?php
					foreach ($pandl['net_expense_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'D');
					}
				?>
			</table>
		</td>

		<td>
			<table>
				<tr>
					<th>Incomes</th>
					<th class="text-right">(Cr) Amount</th>
				</tr>
				<?php
					foreach ($pandl['net_income_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'C');
					}
				?>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table>
				<?php $net_total = $pandl['net_expense_total']; ?>
				<?php if (calculate($pandl['net_expense_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . 'Total Expenses' . '</td>';
					echo '<td class="text-right">' . toCurrency('D', $pandl['net_expense_total']) . '</td>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . 'Total Expenses' . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting Dr Balance">' . toCurrency('D', $pandl['net_expense_total']) . '</td>';
				}
				?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['gross_pl'], 0, '>=')) {
						echo '<td>-</td>';
						echo '<td>-</td>';
					} else {
						$net_total = calculate($net_total, $positive_gross_pl, '+');
						echo '<td>' . 'Gross Loss B/F' . '</td>';
						echo '<td class="text-right">' . toCurrency('', $positive_gross_pl) . '</td>';
					}
					?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['net_pl'], 0, '>=')) {
						echo '<td>' . 'Net Profit' . '</td>';
						echo '<td class="text-right">' . toCurrency('', $pandl['net_pl']) . '</td>';
						$net_total = calculate($net_total, $pandl['net_pl'], '+');
					} else {
						echo '<td>-</td>';
						echo '<td>-</td>';
					}
					?>
				</tr>
				<tr>
					<td>Total</td>
					<td class="text-right"><?php echo toCurrency('', $net_total); ?></td>
				</tr>
			</table>
		</td>

		<td>
			<table>
				<?php $net_total = $pandl['net_income_total']; ?>
				<?php if (calculate($pandl['net_income_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . 'Total Incomes' . '</td>';
					echo '<td class="text-right">' . toCurrency('C', $pandl['net_income_total']) . '</td>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . 'Total Incomes' . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting Cr Balance">' . toCurrency('C', $pandl['net_income_total']) . '</td>';
				}
				?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['gross_pl'], 0, '>=')) {
						$net_total = calculate($net_total, $pandl['gross_pl'], '+');
						echo '<td>' . 'Gross Profit B/F' . '</td>';
						echo '<td class="text-right">' .  toCurrency('', $pandl['gross_pl']) . '</td>';
					} else {
						echo '<td>-</td>';
						echo '<td>-</td>';
					}
					?>
				</tr>
				<tr>
					<?php
					if (calculate($pandl['net_pl'], 0, '>=')) {
						echo '<td>-</td>';
						echo '<td>-</td>';
					} else {
						echo '<td>' . 'Net Loss' . '</td>';
						$positive_net_pl = calculate($pandl['net_pl'], 0, 'n');
						echo '<td class="text-right">' . toCurrency('', $positive_net_pl) . '</td>';
						$net_total = calculate($net_total, $positive_net_pl, '+');
					}
					?>
				</tr>
				<tr>
					<td>Total</td>
					<td class="text-right"><?php echo toCurrency('', $net_total); ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>