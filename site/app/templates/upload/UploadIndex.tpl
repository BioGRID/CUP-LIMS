<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Upload New Experiment <i class='fa fa-lg fa-cloud-upload primaryIcon'></i> </h2>
		<div class='subheadLarge'>Use the following input form to upload and store a new experiment and associated files.</div>
	</div>
</div>

<div class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
			<form id='uploadForm'>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='experimentName' class='control-label'>Experiment Name</label>
					<input type='text' class='form-control' id='experimentName' name='experimentName' placeholder='Experiment Name' />
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='experimentDate' class='control-label'>Run Date (YYYY-MM-DD)</label>
					<input type='text' class='form-control' id='experimentDate' name='experimentDate' placeholder='Run Date (Format: YYYY-MM-DD)' value='{{TODAY}}'	/>
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='experimentCell' class='control-label'>Cell Line</label>
					<select class='form-control' id='experimentCell' name='experimentCell'>
						{% for cellLineID, cellLineName in CELL_LINES %}
							<option value='{{cellLineID}}'>{{cellLineName}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='form-group col-lg-12 col-md-12'>
					<label for='experimentDesc' class='control-label'>Brief Description</label>
					<input type='text' class='form-control' id='experimentDesc' name='experimentDesc' placeholder='Brief Description of Experiment (ex. viability after 15 days, resistance to West Nile Virus)' />
				</div>
				<div id='dropzoneWrap' class=' form-group col-lg-12 col-md-12 marginTopXs'>
					<div class='dropzone' id='dropzoneBox'>
						<div class='dz-message'>Drag/Drop all experiment files here, including background files, or click this box to select each file individually from your file system. <strong>Must be tab-delimited text formatted files...</strong></div>
					</div>
					<input type='hidden' id='experimentCode' name='experimentCode' value='{{DATASET_CODE}}' />
					<input type='hidden' id='experimentHasFile' name='experimentHasFile' value='' />
				</div>
				<div class='form-group col-lg-8 col-md-8'>
					<label for='experimentBG' class='control-label'>Experiment Background (add background files above to populate this list)</label>
					<select class='form-control' id='experimentBG' name='experimentBG' disabled='true' multiple></select>
				</div>
				<div class='marginTopSm col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='experimentUploadBtn' type='submit'><strong>Submit Experiment</strong> <i class='fa fa-check'></i></button>
				</div>
			</form>
		</div>
	</div>
	<div id='messages' class='container-fluid'></div>
</div>

