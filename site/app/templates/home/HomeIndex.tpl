<div class='primaryContent'>
	<div class='container-fluid'>
		<div class='pull-right'>
			<img width='50%' class='pull-right' src='{{ IMG_URL }}/orca-icon-brown.png' alt='ORCA ICON' />
		</div>
		<h2>Welcome to {{ WEB_NAME_ABBR }}</h2>
		<div class='subheadLarge'>Thanks for logging in, <strong>{{FIRSTNAME}} {{LASTNAME}}</strong>. If this is not you, please <strong><a href='{{ WEB_URL }}/Home/Logout' title='Logout of your account'>Logout</a></strong> as soon as possible.</div>

		<hr class='marginTopSm marginBotSm' />
		<div class="alert alert-danger" role="alert" {% if not ALERT_MSG %}style='display:none'{% endif %}>{{ ALERT_MSG | raw }}</div>
		<div class='paddingSm'>
			<h3 class='paddingTopNone'>Getting Started</h3>
			<div class='subheadLarge'>Whether you're new to the site or a regular user, the following tools and categories can help you get started with <strong>{{ WEB_NAME_ABBR }}</strong>.
		</div>
	</div>
</div>

<div class='gettingStartedBox greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<section class="row">
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="panel panel-warning">
					<div class="panel-heading"><strong>Files</strong></div>
					<div class="pull-right"><i class="fa fa-4x fa-file-text paddingLg primaryIcon"></i></div>
					<div class="panel-body">
						Files contain raw experimental data and include both screen data and control files. These form the basis of all view creation and can be combined in a variety of ways to create customized views. If your account has the correct permissions, you can <strong><a href="{{ WEB_URL }}/Upload" title="Upload a new File">upload a new file</a></strong> or you can simply <strong><a href="{{ WEB_URL }}/Files" title="View Files">view a listing</a></strong> of already uploaded files</a></strong>. Below is a listing of recently uploaded files you have access to:
						
						<hr class='marginTopSm marginBotSm' />
						
						<h4>Your Recent Files</h4>
						{% if MY_FILES %}
							<table class='orcaRawReadsTable table table-striped table-bordered table-responsive table-condensed marginTopSm'>
							<thead>
								<tr>
									<th>File</th>
									<th class='text-center'>Size</th>
									<th class='text-center'>State</th>
									<th class='text-center'>Privacy</th>
									<th class='text-center'>Date Added</th>
									<th class='text-center'>User</th>
									<th class='text-center'>Op</th>
								</tr>
							</thead>
							<tbody>
							{% for FILE in MY_FILES %}
								<tr>
									<td><a href='{{ WEB_URL }}/Files/View?id={{ FILE.ID }}' title='View {{ FILE.NAME }}'>{{ FILE.NAME }}</a></td>
									<td class='text-center'>{{ FILE.SIZE }}</td> 
									<td class='text-center'>{{ FILE.STATE | raw }}</td>
									<td class='text-center'>{{ FILE.PERMISSION | raw }}</td>
									<td class='text-center'>{{ FILE.ADDED_DATE }}</td>
									<td class='text-center'>{{ FILE.USER_NAME }}</td>
									<td class='text-center'>{{ FILE.OPTIONS | raw }}</td>
								</tr>
							{% endfor %}
							</tbody>
							</table>
						{% else %}
							<div class='marginBotSm'>
								You have not yet uploaded any files
							</div>
						{% endif %}
						
						<h4>Other Recent Files</h4>
						{% if ALL_FILES %}
							<table class='orcaRawReadsTable table table-striped table-bordered table-responsive table-condensed marginTopSm'>
							<thead>
								<tr>
									<th>File</th>
									<th class='text-center'>Size</th>
									<th class='text-center'>State</th>
									<th class='text-center'>Privacy</th>
									<th class='text-center'>Date Added</th>
									<th class='text-center'>User</th>
									<th class='text-center'>Op</th>
								</tr>
							</thead>
							<tbody>
							{% for FILE in ALL_FILES %}
								<tr>
									<td><a href='{{ WEB_URL }}/Files/View?id={{ FILE.ID }}' title='View {{ FILE.NAME }}'>{{ FILE.NAME }}</a></td>
									<td class='text-center'>{{ FILE.SIZE }}</td> 
									<td class='text-center'>{{ FILE.STATE | raw }}</td>
									<td class='text-center'>{{ FILE.PERMISSION | raw }}</td>
									<td class='text-center'>{{ FILE.ADDED_DATE }}</td>
									<td class='text-center'>{{ FILE.USER_NAME }}</td>
									<td class='text-center'>{{ FILE.OPTIONS | raw }}</td>
								</tr>
							{% endfor %}
							</tbody>
							</table>
						{% else %}
							<div class='marginBotSm'>
								There are no uploaded files
							</div>
						{% endif %}
						
						<div class='text-center'>
							<a href='{{ WEB_URL }}/Files' title='View All Files' class='btn btn-warning btn-sm'>Browse All Files</a>
						</div>
						
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="panel panel-primary">
					<div class="panel-heading"><strong>Views</strong></div>
					<div class="pull-right"><i class="fa fa-4x fa-bar-chart paddingLg primaryIcon"></i></div>
					<div class="panel-body">
						Views are combinations of one or more files distilled into an easily accessible format to aid in discovery. Views come in a variety of formats, each of which can be customized to your requirements. If your account has the correct permissions, you can <strong><a href="{{ WEB_URL }}/View" title="Browse Views">browse existing custom views</a></strong> or you can simply <strong><a href="{{ WEB_URL }}/Files" title="Create a new View">create a new view</a></strong> by seleting files to start with. Below is a listing of recently generated custom views:</a></strong>
						
						<hr class='marginTopSm marginBotSm' />
						
						<h4>Your Recent Custom Views</h4>
						{% if MY_VIEWS %}
							<table class='orcaRawReadsTable table table-striped table-bordered table-responsive table-condensed marginTopSm'>
							<thead>
								<tr>
									<th>View</th>
									<th class='text-center'>Type</th>
									<th class='text-center'>Values</th>
									<th class='text-center'>State</th>
									<th class='text-center'>Privacy</th>
									<th class='text-center'>Date Added</th>
									<th class='text-center'>User</th>
								</tr>
							</thead>
							<tbody>
							{% for VIEW in MY_VIEWS %}
								<tr>
									<td><a href='{{ WEB_URL }}/View?viewID={{ VIEW.ID }}' title='View {{ VIEW.TITLE }}'>{{ VIEW.TITLE }}</a></td>
									<td class='text-center'><i class='fa primaryIcon fa-lg {{ VIEW.TYPE_ICON | raw }}'></i></td> 
									<td class='text-center'>{{ VIEW.VALUE }}</td> 
									<td class='text-center'>{{ VIEW.STATE | raw }}</td>
									<td class='text-center'>{{ VIEW.PERMISSION | raw }}</td>
									<td class='text-center'>{{ VIEW.ADDED_DATE }}</td>
									<td class='text-center'>{{ VIEW.USER_NAME }}</td>
								</tr>
							{% endfor %}
							</tbody>
							</table>
						{% else %}
							<div class='marginBotSm'>
								You have not yet uploaded any experiments
							</div>
						{% endif %}
						
						<h4>Other Recent Custom Views</h4>
						{% if ALL_VIEWS %}
							<table class='orcaRawReadsTable table table-striped table-bordered table-responsive table-condensed marginTopSm'>
							<thead>
								<tr>
									<th>View</th>
									<th class='text-center'>Type</th>
									<th class='text-center'>Values</th>
									<th class='text-center'>State</th>
									<th class='text-center'>Privacy</th>
									<th class='text-center'>Date Added</th>
									<th class='text-center'>User</th>
								</tr>
							</thead>
							<tbody>
							{% for VIEW in ALL_VIEWS %}
								<tr>
									<td><a href='{{ WEB_URL }}/View?viewID={{ VIEW.ID }}' title='View {{ VIEW.TITLE }}'>{{ VIEW.TITLE }}</a></td>
									<td class='text-center'><i class='fa primaryIcon fa-lg {{ VIEW.TYPE_ICON | raw }}'></i></td> 
									<td class='text-center'>{{ VIEW.VALUE }}</td> 
									<td class='text-center'>{{ VIEW.STATE | raw }}</td>
									<td class='text-center'>{{ VIEW.PERMISSION | raw }}</td>
									<td class='text-center'>{{ VIEW.ADDED_DATE }}</td>
									<td class='text-center'>{{ VIEW.USER_NAME }}</td>
								</tr>
							{% endfor %}
							</tbody>
							</table>
						{% else %}
							<div class='marginBotSm'>
								There are no uploaded experiments
							</div>
						{% endif %}
						
						<div class='text-center'>
							<a href='{{ WEB_URL }}/View' title='View all Customized Views' class='btn btn-primary btn-sm'>Browse All Views</a>
						</div>
						
					</div>
				</div>
			</div>
			
		</section>
		<section class="row">
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="panel panel-info">
					<div class="panel-heading"><strong>Upload New Datasets</strong></div>
					<div class="pull-right"><i class="fa fa-4x fa-cloud-upload paddingLg primaryIcon"></i></div>
					<div class="panel-body">
						In order to add new experiments to the {{WEB_NAME_ABBR}} system, you first need to upload the files associated with it. This can be done easily with our <strong><a href="{{ WEB_URL }}/Upload" title="Upload a new Experiment">upload a new experiment</a></strong> tool. With this tool, you can create new experiments and upload both raw data files and control files which are then submitted automatically to our processing queue for inclusion into the database. Once loaded, you can <strong><a href="{{ WEB_URL }}/Experiment" title="Create New views">create new views</a></strong> or simply look at the <strong><a href="{{ WEB_URL }}/Experiment" title="View Experiment">experimental data</a></strong>.
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="panel panel-success">
					<div class="panel-heading"><strong>Read Documentation</strong></div>
					<div class="pull-right"><i class="fa fa-4x fa-book paddingLg primaryIcon"></i></div>
					<div class="panel-body">
						For help on how to use this site, we have provided a <a href="{{ WIKI_URL }}" title="Visit Our Wiki">wiki</a> with documentation on the functionality of various sections of the site. As with any documentation, this area is a work in a progress. If you'd like to help out in making this section even better, please contact the site administrator and request access to add and edit from existing wiki articles.  
					</div>
				</div>
			</div>
		 </section>
		 <section class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel panel-danger">
					<div class="panel-heading"><strong>Administration Tools</strong></div>
					<div class="pull-right"><i class="fa fa-4x fa-gear paddingLg primaryIcon"></i></div>
					<div class="panel-body">
						In addition, your account has been granted admin status over one or more administrative tools due to your permission settings. This allows you to perform a few more tasks that may not be available to your average user. Currently, have permission to 
						{% for ADMIN_TOOL, ADMIN_URL in ADMIN_TOOLS %}
							<strong><a href='{{ ADMIN_URL }}' title='{{ ADMIN_TOOL }}'>{{ ADMIN_TOOL }}</a></strong>,
						{% endfor %}. 
						To view a full list of available <strong>ADMIN</strong> tools, simply click the link <strong>ADMIN</strong> in the top right corner of the navigation bar at the top of this page.
					</div>
				</div>
			</div>
		</section>
		<section class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel panel-default" style="margin-bottom: 5px;">
					<div class="panel-body">
						<div class="pull-left" style="padding-right: 10px;"><i class="fa fa-lg fa-lock primaryIcon"></i></div>
						At any time, simply click on <strong><a href="{{ WEB_URL }}/Home/Logout" title="Logout of Your Account">Logout</a></strong> on here or any page of the site to securely logoff the <strong>{{ WEB_NAME_ABBR }}</strong> website.
					</div>
				</div>
			</div>
		</section>
		<section class="row marginBotXs">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel panel-default" style="margin-bottom: 5px;">
					<div class="panel-body">
						<div class="pull-left" style="padding-right: 10px;"><i class="fa fa-lg fa-globe primaryIcon"></i></div>
						This site requires a modern <strong>HTML 5 compatible browser</strong>. Please use <a href="http://www.mozilla.org/en-US/firefox/new/" title="Get Firefox">Firefox 50+</a>, <a href="https://www.google.com/intl/en/chrome/browser/" title="Get Chrome">Chrome 50+</a>, <a href="https://www.microsoft.com/en-us/windows/microsoft-edge" title="Get Internet Explorer">Microsoft Edge</a>, or <a href="http://www.opera.com/" title="Get Opera">Opera 42+</a>. 
					</div>
				</div>
			</div>
		</section>
		<section class="row marginBotXs">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel panel-default" style="margin-bottom: 5px;">
					<div class="panel-body">
						<div class="pull-left" style="padding-right: 10px;"><i class="fa fa-lg fa-handshake-o primaryIcon"></i></div>
						The {{ WEB_NAME_ABBR }} website and all associated tools are provided "as is" and without any warranty or support under the <strong><a href='https://opensource.org/licenses/MIT' title='MIT Open Source License'>MIT Open Source License</a></strong> and are archived at <a href='https://github.com/BioGRID' title='BioGRID GitHub'>GitHub</a>. This project is generously funded by grants from the <a href="http://www.nih.gov/" title="NIH">National Institutes of Health</a>, <a href="http://www.cihr-irsc.gc.ca/" title="CIHR">Canadian Institutes of Health Research</a>, and <a href='http://www.genomequebec.com/' title='Genome Quebec'>Genome Quebec</a> as part of the <a href='https://thebiogrid.org' title='The BioGRID'>BioGRID</a> family of bioinformatics tools.
					</div>
				</div>
			</div>
		</section>
	</div>
</div>