
<div id='permissionDetailsWrap' class='greyBG marginTopSm paddingLg marginBotSm' style='display: none;'>
	<div class='container-fluid'>
		<h3>View Permissions</h3>
		<div id='viewPermissionBox' class='form-group col-lg-12 col-md-12 marginTopSm'>
			<label for='viewPermission' class='control-label'>View Permissions (who can see this view)</label>
			<select class='form-control' id='viewPermission' name='viewPermission'>
				<option value='public'>Public (Openly Available to all Users, Best for Views Involving Published Datasets)</option>
				{% if IS_PRIVATE %}
					<option value='private' selected>Private (Available Only to the Owner, and Groups of Users You Select)</option>
				{% else %}
					<option value='private'>Private (Available Only to the Owner, and Groups of Users You Select)</option>
				{% endif %}
			</select>
		</div>
		<div id='viewGroupsBox' class='form-group col-lg-8 col-md-8' {% if not IS_PRIVATE %}style='display:none'{% endif %}>
			<label for='viewGroups' class='control-label'>Permitted Groups (if you don't choose any, only the owner will have access to this file) [choose all that apply]</label>
			<select class='form-control' id='viewGroups' name='viewGroups' multiple>
				{% for groupID, groupInfo in GROUPS %}
					<option value='{{groupID}}'
					{% if groupID in SELECTED_GROUPS %} selected{% endif %}
					>{{groupInfo.group_name}}</option>
				{% endfor %}
			</select>
		</div>
		<input type='hidden' id='viewID' value='{{ VIEW_ID }}' />
		<div class='col-lg-12 col-md-12'>
			<button class='btn btn-success btn-lg' id='permissionChangeBtn' type='submit'><strong>Update Permissions</strong> <i class='fa fa-check'></i></button>
		</div>
	</div>
	<div id='messages' class='container-fluid marginTopSm'></div>
</div>