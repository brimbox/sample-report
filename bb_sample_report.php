<?php
/*
 * Copyright Brimbox LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 3 (GNU GPL v3)
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU GPL v3 for more details.
 *
 * You should have received a copy of the GNU GPL v3 along with this program.
 * If not, see http://www.gnu.org/licenses/
 */

/* SAMPLE REPORT MODULE */
/* The below is sample report to go with the sample data */

/*
@module_name = bb_sample_report;
@friendly_name = Report;
@interface = bb_brimbox;
@module_type = 3;
@module_version = 2.0;
@description = This is a sample report which can be used as a template for building custom reports and modules. This is a sample report which can be used = as a template for building custom reports and modules.;
*/
?>

<?php
// double check permission
$main->check_permission(array("3_bb_brimbox","4_bb_brimbox","5_bb_brimbox"));

/* BEGIN STATE AND POSTBACK PROCESS */
// get state variables
$POST = $main->retrieve ( $con );

// get state from db
$arr_state = $main->load ( $con, $module );

// module_display and module_submit are the same in this example
$current = $main->report_post ( $arr_state, $module, $module );
$button = $current ['button'];

// handle custom report charge variable
$charge_type = $main->process ( 'charge_type', $module, $arr_state, "" );

// update state, back to db
$main->update ( $con, $module, $arr_state );
/* END STATE PROCESS */

/* GET DATA AND SET REPORT PARAMS */
// prepare query with dropdown variable applied to WHERE clause
$result = false; // prevent strict notice
if ($main->button(1)) {
	if ($charge_type == "All") {
		$where_clause = "1 = 1";
	} else {
		$where_clause = "c03 = '" . $charge_type . "'";
	}
	
	// get order by for sort, the names column will be sortable
	$order_by = $main->build_sort ( $current );
	
	// query string with where clause
	$query = "SELECT T1.c01 as Name, T1.c02 as Breed, T1.c03 as Location, T1.c04 as Birthday, T2.Total FROM data_table T1 " .
	"LEFT JOIN (SELECT key1, sum(c02::numeric(15,2)) as Total FROM data_table  WHERE " . $where_clause . "  AND row_type = 2 GROUP BY key1) T2 " .
	"ON T1.id = T2.key1 WHERE T1.row_type = 1 and T1.archive IN (0) " . $order_by . ";";
	//echo "<p>" . $query . "</p>";
	// execute query
	$result = $main->query ( $con, $query );
	
	// setup report parameters
	// first array key is report type
	// second array key references report row type
	$settings [1] [0] = array (
			'ignore' => true,
			'limit' => 4,
			'shade_rows' => true,
			'title' => 'Aggregated Credits and Debits',
			's00' => 'T1.c01',
			'ucfirst' => true
	);
	$settings [2] [0] = array (
			'ignore' => true,
			'shade_rows' => true,
			'title' => 'Aggregated Credits and Debits',
			's00' => 'T1.c01',
			'ucfirst' => true
	);
	$settings [3] [0] = array (
			'rows' => 15,
			'columns' => 30,
			'title' => 'Aggregated Credits and Debits' 
	);
}

if ($main->button(2)) {
	
	// get order by for sort, the names column will be sortable
	$order_by = $main->build_sort ( $current );
	
	// query string with where clause
	$query = "SELECT T1.c01 as Name, T1.c02 as Breed, T1.c03 as Location, T1.c04 as Birthday, T2.c03 as \"Charge Type\", T2.c02 as Amount FROM data_table T1 " .
	"LEFT JOIN (SELECT key1, c03, c02::numeric(15,2) as c02 FROM data_table WHERE row_type = 2) T2 " .
	"ON T1.id = T2.key1 WHERE T1.row_type = 1 and T1.archive IN (0) " . $order_by . ";";
	//echo "<p>" . $query . "</p>";
	// execute query
	$result = $main->query ( $con, $query );
	
	// setup report parameters
	// first array key is report type
	// second array key references report row type
	$settings [1] [0] = array (
			'ignore' => true,
			'limit' => 4,
			'shade_rows' => true,
			'title' => 'Return Charges',
			's00' => 'T1.c01',
			'ucfirst' => true
	);
	$settings [2] [0] = array (
			'ignore' => true,
			'shade_rows' => true,
			'title' => 'Return Charges',
			's00' => 'T1.c01',
			'ucfirst' => true
	);
	$settings [3] [0] = array (
			'rows' => 15,
			'columns' => 30,
			'title' => 'Return Charges' 
	);
}
/* END GET DATA */
/* END GET DATA */

/* ECHO REPORT, REQUIRED FORM, AND REPORT FORM VARIABLES */
// echo report header
echo "<p class=\"spaced bold larger\">Sample Report</p>";

// Start Required Form
// echo form with normal Brimbox vars
echo "<div class=\"inlineblock spaced twice border\">";

$main->echo_form_begin ();
$main->echo_module_vars ();

// echo form report vars for report functions
$main->echo_report_vars ();

// Standard Report Form Variables
// report type dropdown, use $pass as resuable parameters variable
$main->echo_tag("label", "Report Type:  ", array('class'=>"padded"));
$params = array ("class" => "margin");
$main->report_type ( $current ['report_type'], $params );
echo "<br>";
// Report Variables and Button
// charge type selector
$params = array ("number" => 1,"label" => "Aggregated Credits and Debits", "class" => "margin");
$main->echo_button ( "sample_aggregated", $params );
$arr_charge = array ("Credit","Debit");
$main->echo_tag("label", "Charge Type:  ", array('class'=>"padded"));
$params = array ("class" => "margin");
// charge type dropdown
$main->array_to_select ( $arr_charge, "charge_type", $charge_type, array (), $params );
// report execution button
echo "<br>";
$params = array ("number" => 2,"label" => "Return Charges", "class" => "margin");
$main->echo_button ( "sample_charges", $params );
// End Report Form Variables
// End Report Form Variables

// end form
$main->echo_form_end ();
// End Required Form

echo "</div>";

// output report outside form
if ($result) {
	$main->output_report ( $result, $current, $settings );
}
/* END ECHO REPORT */
?>
