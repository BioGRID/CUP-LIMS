<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Upload Files <i class='fa fa-lg fa-cloud-upload primaryIcon'></i> </h2>
		<div class='subheadLarge'>Use the following input form to upload and store new raw data and control files.</div>
	</div>
</div>

<div class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>
			<form id='uploadForm'>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='fileDesc' class='control-label'>File Set Name</label>
					<input type='text' class='form-control' id='fileDesc' name='fileDesc' placeholder='File Set Name' />
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='fileDate' class='control-label'>Run Date (YYYY-MM-DD)</label>
					<input type='text' class='form-control' id='fileDate' name='fileDate' placeholder='Run Date (Format: YYYY-MM-DD)' value='{{TODAY}}'	/>
				</div>
				<div class='form-group col-lg-4 col-md-4'>
					<label for='fileAnnotation' class='control-label'>Default Annotation Mapping</label>
					<select class='form-control' id='fileAnnotation' name='fileAnnotation'>
						{% for annotationID, annotationInfo in ANNOTATION_FILES %}
							<option value='{{annotationID}}'>{{annotationInfo.annotation_file_name}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='form-group col-lg-12 col-md-12'>
					<label for='fileTags' class='control-label'>Reference Tags</label>
					<input type='text' class='form-control' id='fileTags' name='fileTags' placeholder='Comma separated set of tags for searching (example: ubiquitin, GBM, sabatini) [optional]' />
				</div>
				<div id='dropzoneWrap' class=' form-group col-lg-12 col-md-12 marginTopXs'>
					<div class='dropzone' id='dropzoneBox'>
						<div class='dz-message'>Drag/Drop all files here, including control files, or click this box to select each file individually from your file system. <strong>Must be tab-delimited text formatted files...</strong></div>
					</div>
					<input type='hidden' id='fileCode' name='fileCode' value='{{DATASET_CODE}}' />
					<input type='hidden' id='hasFile' name='hasFile' value='' />
				</div>
				<div class='form-group col-lg-8 col-md-8'>
					<label for='fileBG' class='control-label'>Control Files (add control files above, and select them when they populate this list) [choose all that apply]</label>
					<select class='form-control' id='fileBG' name='fileBG' disabled='true' multiple></select>
				</div>
				<div class='form-group col-lg-12 col-md-12'>
					<label for='filePermission' class='control-label'>File Permissions (who can see these files)</label>
					<select class='form-control' id='filePermission' name='filePermission'>
						<option value='public'>Public (Openly Available to all Users, Best for Published Datasets)</option>
						<option value='private'>Private (Available Only to You, and Groups of Users You Select)</option>
					</select>
				</div>
				<div id='fileGroupsBox' class='form-group col-lg-8 col-md-8' style='display: none'>
					<label for='fileGroups' class='control-label'>Permitted Groups (if you don't choose any, only YOU alone will have access to these files) [choose all that apply]</label>
					<select class='form-control' id='fileGroups' name='fileGroups' multiple>
						{% for groupID, groupInfo in GROUPS %}
							<option value='{{groupID}}'>{{groupInfo.group_name}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='marginTopSm col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='fileUploadBtn' type='submit'><strong>Submit Files</strong> <i class='fa fa-check'></i></button>
				</div>
			</form>
		</div>
	</div>
	<div id='messages' class='container-fluid'></div>
</div>

