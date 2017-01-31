<div class='primaryContent'>
	<div class='container-fluid'>
	
		<div class='pull-right col-lg-3 col-md-4 col-sm-5 col-xs-6'>
			<form class="form-search" role="form" method="GET" action="{{ WEB_URL }}/Dataset/">
				<div class='input-group marginBotSm marginTopSm'>
					<input type="text" name='datasetID' id='datasetID' maxlength='8' class="form-control" placeholder="Search Experiments" value="" autofocus>
					<span class='input-group-btn'>
						<button class='btn btn-success' type='submit'>Search</button>
					</span>
				</div>
			</form>
		</div>
	
		<h3>{{ WEB_NAME_ABBR }} Dashboard</h3>
		<div class='subhead'>Welcome Back - <strong>{{FIRSTNAME}} {{LASTNAME}}</strong></div>
	</div>
</div>

<div class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='text-center'>
			<a href="#" class="shortcut-link">
				<span class="shortcut-icon">
					<i class="fa fa-hourglass-half"></i>
					<span class="shortcut-alert">
						17
					</span>	
				</span>
				<span class="text">Files In Progress</span>
			</a>
			<a href="#" class="shortcut-link">
				<span class="shortcut-icon">
					<i class="fa fa-envelope-o"></i>
					<span class="shortcut-alert">
						9
					</span>	
				</span>
				<span class="text">Messages</span>
			</a>
			<a href="#" class="shortcut-link">
				<span class="shortcut-icon">
					<i class="fa fa-bell"></i>
					<span class="shortcut-alert">
						6
					</span>	
				</span>
				<span class="text">Alerts</span>
			</a>
			<a href="{{ WEB_URL }}/Admin" class="shortcut-link">
				<span class="shortcut-icon">
					<i class="fa fa-gear"></i>
				</span>
				<span class="text">Settings</span>
			</a>
		</div>
	</div>

</div>

<div>
	<div class='container-fluid'>
		<div class='col-lg-6 col-md-6 col-sm-6'>
			<div class='high-queue-panel'>
				<i class="fa fa-flask fa-3x pull-right"></i>
				<h3><strong>145</strong></h3>
				<h5>Uploaded Experiments<h5>
				<hr />
				<div style='margin-top: 10px;'>
					<h4>Recently Added Experiments</h4>
					<ul style='margin-top: 5px; font-size: 18px;'>
						<li>HI.3215 - High Dose [19 Raw Data Files] <i class='fa fa-search-plus' style='color: #655643'></i></li>
						<li>HI.3215 - Low Dose [21 Raw Data Files] <i class='fa fa-search-plus' style='color: #655643'></i></li>
						<li>HI.2145 - High Dose [15 Raw Data Files] <i class='fa fa-search-plus' style='color: #655643'></i></li>
					</ul>
				</div>
			</div>
		</div>
		<div class='col-lg-6 col-md-6 col-sm-6'>
			<div class='standard-queue-panel'>
				<i class="fa fa-bar-chart fa-3x pull-right"></i>
				<h3><strong>137</strong></h3>
				<h5>Custom Views<h5>
				<hr />
				<div style='margin-top: 10px;'>
					<h4>Recently Completed Custom Views</h4>
					<ul style='margin-top: 5px; font-size: 18px;'>
						<li>HI.3215 - High Dose / ALL FILES <span class='text-success'><strong>[Matrix]</strong></span> <span class='text-info'><strong>[0day-reads background]</strong></span></li>
						<li>HI.3215 - High Dose / ALL FILES <span class='text-danger'><strong>[HeatMap]</strong></span> <span class='text-info'><strong>[7dox8 background]</strong></span></li>
						<li>HI.3215 - Low Dose / 8 Files <span class='text-danger'><strong>[HeatMap]</strong></span> <span class='text-info'><strong>[0day-reads background]</strong></span></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>