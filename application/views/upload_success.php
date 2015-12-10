
<?php
for ($i = 0;$i < sizeof($tempstrs);$i++) {
	echo $tempstrs[$i];
	echo '<br>';
}
echo 'BC SIZE:'.sizeof($table_names_new);

for ($i = 0;$i < sizeof($table_names_new);$i++) {
	echo '<li>' .$table_names_new[$i] .': '. $table_types_new[$i] . '-'. $table_descs_new[$i].'</li><br>';
}

?>
