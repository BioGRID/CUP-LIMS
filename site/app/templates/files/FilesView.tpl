<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>{{ FILE_NAME }} <i class='fa fa-lg fa-file-text primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following information is available for file <span class='text-success'><strong>{{ FILE_NAME }}</strong></span></div>
	</div>
</div>

<div id='viewDetailsWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='pull-right'>
			<div class='viewDetail'><strong>Date Added: </strong> {{ FILE_ADDEDDATE }}</div>
			<div class='viewDetail'><strong>Parsed State: </strong> {{ FILE_STATE }}</div>
			<div class='viewDetail'><strong>Total Reads: </strong> {{ FILE_READTOTAL }}</div>
			<div class='viewDetail'><strong>File Size: </strong> {{ FILE_SIZE }}</div>
			<div class='viewDetail'><strong>User: </strong> {{ USER_NAME }}</div>
		</div>
		<h3>File Details</h3>
		<div class='subheadLarge'>Use the correct button below to download this file in various supported formats.</div>
		<div class='marginTopSm'>
			<a href='{{ UPLOAD_PROCESSED_URL }}/{{ FILE_CODE }}/{{ FILE_NAME }}' title='Download {{ FILE_NAME }}'><button class='btn btn-info btn-lg'><i class="text-primary fa fa-cloud-download fa-lg"></i> Download Original Raw File</button></a>
			{% if VIEW_STATE != "building" %}
				<a href='{{ WEB_URL }}/View/Download?viewID={{ VIEW_ID }}' title='View Download'><button class='btn btn-orca2 btn-lg'><i class="fa fa-cloud-download fa-lg" style='color: #efbc2b'></i> Download Raw File with Annotation</button></a>
			{% endif %}
		</div>
	</div>
</div>

{% if CAN_EDIT %}
<div id='permissionDetailsWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<h3>File Permissions</h3>
		<div id='filePermissionBox' class='form-group col-lg-12 col-md-12 marginTopSm'>
			<label for='filePermission' class='control-label'>File Permissions (who can see this file)</label>
			<select class='form-control' id='filePermission' name='filePermission'>
				<option value='public'>Public (Openly Available to all Users, Best for Published Datasets)</option>
				{% if IS_PRIVATE %}
					<option value='private' selected>Private (Available Only to the Owner, and Groups of Users You Select)</option>
				{% else %}
					<option value='private'>Private (Available Only to the Owner, and Groups of Users You Select)</option>
				{% endif %}
			</select>
		</div>
		<div id='fileGroupsBox' class='form-group col-lg-8 col-md-8' {% if not IS_PRIVATE %}style='display:none'{% endif %}>
			<label for='fileGroups' class='control-label'>Permitted Groups (if you don't choose any, only the owner will have access to this file) [choose all that apply]</label>
			<select class='form-control' id='fileGroups' name='fileGroups' multiple>
				{% for groupID, groupInfo in GROUPS %}
					<option value='{{groupID}}'
					{% if groupID in SELECTED_GROUPS %} selected{% endif %}
					>{{groupInfo.group_name}}</option>
				{% endfor %}
			</select>
		</div>
		<input type='hidden' id='fileID' value='{{ FILE_ID }}' />
		<div class='col-lg-12 col-md-12'>
			<button class='btn btn-success btn-lg' id='permissionChangeBtn' type='submit'><strong>Update Permissions</strong> <i class='fa fa-check'></i></button>
		</div>
	</div>
	<div id='messages' class='container-fluid marginTopSm'></div>
</div>
{% endif %}

{% if VIEW_STATE != "building" %}
	{% include 'blocks/ORCADataTableBlock.tpl' %}
{% else %}
	{% include 'files/FilesViewLoading.tpl' %}
{% endif %}

<input type='hidden' id='viewID' value='{{ VIEW_ID }}' />
<input type='hidden' id='viewState' value='{{ VIEW_STATE }}' />