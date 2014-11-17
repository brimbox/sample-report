<?php
/*
Copyright (C) 2012  Kermit Will Richardson, Brimbox LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License Version 3 (“GNU GPL v3”)
as published by the Free Software Foundation. 

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU GPL v3 for more details. 

You should have received a copy of the GNU GPL v3 along with this program.
If not, see http://www.gnu.org/licenses/
*/

/* SAMPLE REPORT MODULE */
/* The below is sample report to go with the sample data */

/*
@module_name = bb_sample_report;
@friendly_name = Reports;
@interface = bb_brimbox;
@module_type = Tab;
@module_version = 1.1;
@maintain_state = Yes;
@description = This is a report used for the webdemo.;
*/
?>

<?php
//double check permission
$main->check_permission('bb_brimbox', array(3,4,5));

//get state variables
$main->retrieve($con, $array_state, $userrole);

//handle state           
$json_state = $main->load($module, $array_state);

//get standard report variables used in report, module_display and module_submit are the same
$current = $main->report_post($json_state, $module, $module);

$charge_type = $main->process('charge_type', $module, $json_state, "");

//update state
$main->update($array_state, $module, $json_state);

//prepare query with dropdown variable applied to WHERE clause
if ($current['button'] == 1)
    {
    if ($charge_type == "All")
        {
        $where_clause = "1 = 1";
        }
    else 
        {
        $where_clause = "T2.c03 = '" . $charge_type . "'";    
        }
    //get order by for sort, the names column will be sortable    
	$order_by = $main->build_sort($current);

	//query string with where clause     
    $query = "SELECT T4.c01 as Name, T4.c02 as Breed, T4.c03 as Owner, T4.c04 as Birthday, T4.c05 as Location, T4.c06 as Type, T3.Total FROM data_table T4 LEFT JOIN (SELECT T1.id, T1.c01 as Name, sum(T2.c02::numeric(15,2)) as Total FROM data_table T1, data_table T2 " .
             "WHERE T1.id = T2.key1 AND " . $where_clause . " AND T1.row_type = 1 AND T2.row_type = 2 GROUP BY T1.c01, T1.id) T3 ON t3.id = T4.id WHERE T4.row_type = 1 and archive IN (0) " . $order_by . ";";
    //echo "<p>" . $query . "</p>";
	//execute query
    $result = $main->query($con, $query);
	
	//setup report parameters	
	$settings[1][0] = array('ignore'=>true,'limit'=>4,'shade_rows'=>true,'title'=>'Return Charges','s00'=>'T4.c01','s01'=>'T4.c02');
	$settings[2][0] = array('ignore'=>true,'shade_rows'=>true,'title'=>'Return Charges','s00'=>'T4.c01','s01'=>'T4.c02');
	$settings[3][0] = array('rows'=>40,'columns'=>80,'title'=>'Return Charges');	
    }
	
if ($current['button'] == 2)
    {
		
//query string with where clause
$query = <<<EOT
	WITH selectquery as
		(SELECT 0 as sort, c01, c02, c03 ,c04, c05, c06, T2.c07, T2.c08::numeric(20,2) FROM 
		(SELECT * FROM data_table WHERE row_type = 1) T1
		INNER JOIN
		(SELECT key1, c03 as c07, c02 as c08 FROM data_table WHERE row_type = 2) T2
		ON T1.id = T2.key1
		UNION 
		SELECT 1, null, null, null ,null, null, null, T3.c07, sum(T3.c08::float)::numeric(20,2) FROM 
		((SELECT id FROM data_table WHERE row_type = 1) T1
		INNER JOIN
		(SELECT key1, c03 as c07, c02 as c08 FROM data_table WHERE row_type = 2) T2
		ON T1.id = T2.key1) T3
		GROUP BY T3.c07
		UNION 
		SELECT 2, null, null, null ,null, null, null, null, sum(T3.c08::float)::numeric(20,2) FROM 
		((SELECT id FROM data_table WHERE row_type = 1) T1
		INNER JOIN
		(SELECT key1, c03 as c07, c02 as c08 FROM data_table WHERE row_type = 2) T2
		ON T1.id = T2.key1) T3)
	SELECT sort, c01 as Name, c02 as Breed, c03 as Type, c04 as Birthday, c05 as Owner, c06 as Location, c07 as Charge, c08 as Amount
	FROM selectquery ORDER BY c07, sort, c01
EOT;

	//echo "<p>" . $query . "</p>";
	//execute query
    $result = $main->query($con, $query);
	
	//setup report parameters	
	$settings[1][0] = array('limit'=>5,'title'=>"Return With Totals",'start_column'=>1, "c08" => "right extra", 'count'=>true);
	$settings[1][1] = array('t01' => "Total By Type", 'c08' => "right extra");	
	$settings[1][2] = array('t01' => "Total", 'c08' => "right extra");
	
	$settings[2][0] = array('title'=>"Return With Totals",'start_column'=>1, 'c08' => "right extra", 'count'=>true);
	$settings[2][1] = array('t01' => "Total By Type", 'c08' => "right extra");
	$settings[2][2] = array('t01' => "Total", 'c08' => "right extra");

	$settings[3][0] = array('rows'=>40,'columns'=>80,'title'=>"Return With Totals",'start_column'=>1);
	$settings[3][1] = array('t01' => "Total By Type");
	$settings[3][2] = array('t01' => "Total");
	}

//echo report header
echo "<p class=\"spaced bold larger\">Sample Report</p>";

//echo form report, report must be echoed within the form
$main->echo_form_begin();
$main->echo_module_vars($module);
$main->echo_report_vars();

//report type dropdown, use $params as resuable parameters variable
$pass = array("select_class" => "spaced", "label_class" => "padded", "label" => "Report Type: ");
$main->report_type($current['report_type'], $pass);
echo "<br>";

//report 1 execution
echo "<div class=\"border padded spaced floatleft\">";
$pass = array("number" => 1, "label" => "Submit Report","class" => "margin");
$main->echo_button("sample_report", $pass);
$arr_charge = array("Credit","Debit");
$pass = array("all" => true, "label" => "Charge Type: ","select_class" => "margin", "label_class" => "padded");
//charge type dropdown
$main->array_to_select($arr_charge, "charge_type", $charge_type, $pass);
echo "</div>";
echo "<div class=\"clear\"></div>";

//report 2 execution button
echo "<div class=\"border padded spaced floatleft\">";
$pass = array("number" => 2, "label" => "Submit Grouped Report","class" => "margin");
$main->echo_button("sample_report", $pass);
echo "</div>";
echo "<div class=\"clear\"></div>";

//end form	
$main->echo_state($array_state);
$main->echo_form_end();

//output report	
if (isset($result))
	{	
	$main->output_report($result, $current, $settings);
	}
?>
