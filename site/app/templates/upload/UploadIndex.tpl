<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Upload New Dataset</h2>
		<div class='subheadLarge'>Use the following input form to upload and store a new dataset.</div>
	</div>
</div>

<div class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
			<form id='uploadForm'>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='datasetName' class='control-label'>Dataset Name</label>
					<input type='text' class='form-control' id='datasetName' name='datasetName' placeholder='Dataset Name' />
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='datasetDate' class='control-label'>Run Date (YYYY-MM-DD)</label>
					<input type='text' class='form-control' id='datasetDate' name='datasetDate' placeholder='Run Date (Format: YYYY-MM-DD)' value='{{TODAY}}'	/>
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='datasetCell' class='control-label'>Cell Line</label>
					<select class='form-control' id='datasetCell'>
						{% for cellLineID, cellLineName in CELL_LINES %}
							<option value='{{cellLineID}}'>{{cellLineName}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='form-group col-lg-12 col-md-12'>
					<label for='datasetDesc' class='control-label'>Brief Description</label>
					<input type='text' class='form-control' id='datasetDesc' name='datasetDesc' placeholder='Brief Description of Experiment (ex. viability after 15 days, resistance to West Nile Virus)' />
				</div>
				<div id='dropzoneWrap' class=' form-group col-lg-12 col-md-12 marginTopXs'>
					<div class='dropzone' id='dropzoneBox'>
						<div class='dz-message'>Drag/Drop dataset file here or click to select manually from file system...</div>
					</div>
					<input type='hidden' id='datasetCode' name='datasetCode' value='{{DATASET_CODE}}' />
					<input type='hidden' class='form-control' id='datasetFile' name='datasetFile' value='' />
				</div>
				<div class='marginTopSm col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='datasetUploadBtn' type='submit'><strong>Submit Dataset</strong> <i class='fa fa-check'></i></button>
				</div>
			</div>
		</div>
	</div>
</div>