<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Upload Annotation <i class='fa fa-lg fa-adn primaryIcon'></i> </h2>
		<div class='subheadLarge'>Use the following input form to upload and store sgRNA annotation mapping files.</div>
	</div>
</div>

<div class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
			<form id='uploadForm'>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='annotationDesc' class='control-label'>Annotation Description</label>
					<input type='text' class='form-control' id='annotationDesc' name='annotationDesc' placeholder='Short Description of Annotation File' />
				</div>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='annotationOrganism' class='control-label'>Organism</label>
					<select class='form-control' id='annotationOrganism' name='annotationOrganism'>
						{% for organismID, organismName in ORGANISMS %}
							<option value='{{organismID}}'>{{organismName}}</option>
						{% endfor %}
					</select>
				</div>
				<div id='dropzoneWrap' class=' form-group col-lg-12 col-md-12 marginTopXs'>
					<div class='dropzone' id='dropzoneBox'>
						<div class='dz-message'>Drag/Drop annotation file here, or click this box to select the file individually from your file system. <strong>Must be a tab-delimited annotation formatted file...</strong></div>
					</div>
					<input type='hidden' id='fileCode' name='fileCode' value='{{DATASET_CODE}}' />
					<input type='hidden' id='hasFile' name='hasFile' value='' />
				</div>
				<div class='marginTopSm col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='annotationUploadBtn' type='submit'><strong>Submit Annotation</strong> <i class='fa fa-check'></i></button>
				</div>
			</form>
		</div>
	</div>
	<div id='messages' class='container-fluid marginSmTop'></div>
</div>

