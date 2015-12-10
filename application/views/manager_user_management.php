<h1>User management</h1>


<input class="search" id="researchers-search" placeholder="Search">

<div class="pager">
	<a href="#" class="prev">&laquo; Previous</a>
	<a href="#" class="next">Next &raquo;</a>
	<select class="pagesize">
		<option value="50">50</option>
	</select>
</div>

<table id="user-management" class="tablesorter">
	<thead> 
		<tr>
			<th id='name-title'>NAME</th>
			<th id='email-title'>EMAIL</th>
			<th id='developer-title'>DEVELOPER</th>
			<th id='researcher-title'>RESEARCHER</th>
			<th id='manager-title'>MANAGER</th>
			<th id='active-title'>STATUS</th>
		</tr>
	</thead>
	
	<tbody>
	</tbody>
	
</table>

<div class="pager">
	<a href="#" class="prev">&laquo; Previous</a>
	<a href="#" class="next">Next &raquo;</a>
</div>

<?php
	echo form_open();
	echo form_close();
?>