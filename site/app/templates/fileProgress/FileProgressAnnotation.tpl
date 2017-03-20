<div id='fileProgress'>
	<div class='primaryContent'>
		<div class='container-fluid'>
			<h2>Annotation File Progress <i class='fa fa-lg fa-server primaryIcon'></i> </h2>
			<div class='subheadLarge'>The annotation file below is being processed to the database. Please be patient, this process may take a while to complete and this page will update regularly with progress. Once your file is indicated as completed, you can proceed to utilize this annotation when processing raw data files. This Page will auto refresh every <strong>30 seconds</strong> or you can manually refresh at any time.</div>
		</div>
	</div>

	<div class='marginTopSm marginBotSm'>
		<div class='container-fluid'>
			<h3 class='marginBotSm'>Overall Progress</h3>
			<div id='processingProgress'>
				<div class="progress marginBotSm">
					<div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width: {{ PROGRESS_PERCENT }}%">
						<span>{{ PROGRESS_PERCENT }}% Complete</span>
					</div>
				</div>
				<div class='progressSummary'>
					<div class='pull-right statusStats'>[<span class='text-info'>{{ QUEUED_FILES }}</span> Queued, <span class='text-warning'>{{ INPROGRESS_FILES }}</span> In Progress, <span class='text-success'>{{ SUCCESS_FILES }}</span> Success, <span class='text-danger'>{{ ERROR_FILES }}</span> Errors]</div>
					{{ COMPLETED_FILES }} of {{ TOTAL_FILES }} Total Files Processed
				</div>
			</div>
			<div>
				
			</div>
		</div>
	</div>

	{% if FILE_INPROGRESS %}
		<div class='greyBG marginTopSm paddingLg marginBotSm'>
			<div class='container-fluid'>
				<h3 class='marginBotSm'>Files Currently In Progress</h3>
				<div id='processingOutput'>{{FILE_INPROGRESS|raw}}</div>
			</div>
		</div>
	{% endif %}
	
	{% if FILE_COMPLETED %}
		<div class='greyBG marginTopSm paddingLg marginBotSm'>
			<div class='container-fluid'>
				<h3 class='marginBotSm'>Files Completed</h3>
				<div id='processingOutput'>{{FILE_COMPLETED|raw}}</div>
			</div>
		</div>
	{% endif %}
	
	{% if FILE_QUEUED %}
		<div class='greyBG marginTopSm paddingLg marginBotSm'>
			<div class='container-fluid'>
				<h3 class='marginBotSm'>Files Queued for Processing</h3>
				<div id='processingOutput'>{{FILE_QUEUED|raw}}</div>
			</div>
		</div>
	{% endif %}
</div>

<input type='hidden' id='isRunning' value='{{ IS_RUNNING }}' />