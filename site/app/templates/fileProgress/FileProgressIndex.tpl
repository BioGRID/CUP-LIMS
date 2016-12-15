<div id='fileProgress'>
	<div class='primaryContent'>
		<div class='container-fluid'>
			<h2>Experiment File Progress <i class='fa fa-lg fa-server primaryIcon'></i> </h2>
			<div class='subheadLarge'>The files for Experiment: <strong>{{ EXPERIMENT_NAME }}</strong> are being processed to the database. Please be patient, this process may take a wile to complete and this page will update regularly with progress. Once all your files are indicated as completed, you can proceed to analyze your experiment. Page will auto refresh every <strong>30 seconds</strong> or you can manually refresh at any time.</div>
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
					{{ COMPLETED_FILES }} of {{ TOTAL_FILES }} Total Files Processed
				</div>
			</div>
			<div>
				
			</div>
		</div>
	</div>

	<div class='greyBG marginTopSm paddingLg marginBotSm'>
		<div class='container-fluid'>
			<div class='pull-right statusStats'>[<span class='text-info'>{{ QUEUED_FILES }}</span> Queued, <span class='text-warning'>{{ INPROGRESS_FILES }}</span> In Progress, <span class='text-success'>{{ SUCCESS_FILES }}</span> Success, <span class='text-danger'>{{ ERROR_FILES }}</span> Errors]</div>
			<h3 class='marginBotSm'>Individual File Status</h3>
			<div id='processingOutput'>{{FILE_PROGRESS|raw}}</div>
		</div>
	</div>
</div>

<input type='hidden' id='isRunning' value='{{ IS_RUNNING }}' />