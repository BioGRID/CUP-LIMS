<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Create View <i class='fa fa-lg fa-bar-chart primaryIcon'></i> </h2>
		<div class='subheadLarge'>Fill in the following form with details for your view, and select files to include from the table listing below.</div>
	</div>
</div>

<div id='addViewWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<h3>View Settings</h3>
		<span id='addNewViewSubhead' class='subheadSmall'>Use this form to create a new custom view of your data</span>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 marginTopSm clearfix'>
			<form id='addViewForm'>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='viewName' class='control-label'>View Name</label>
					<input type='text' class='form-control' id='viewName' name='viewName' placeholder='A short memorable name describing the view' />
				</div>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='viewDesc' class='control-label'>View Description</label>
					<input type='text' class='form-control' id='viewDesc' name='viewDesc' placeholder='A more detailed but short description of the view'	/>
				</div>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='viewType' class='control-label'>View Type</label>
					<select class='form-control' id='viewType' name='viewType'>
						{% for OPT_ID,OPT_VALUE in VIEW_TYPES %}
							{% if OPT_ID != "2" %}
								<option value='{{OPT_ID}}'>{{OPT_VALUE}}</option>
							{% endif %}
						{% endfor %}
					</select>
				</div>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='viewValue' class='control-label'>View Value</label>
					<select class='form-control' id='viewValue' name='viewValue'>
						{% for OPT_ID,OPT_VALUE in VIEW_VALUES %}
							{% if OPT_ID != "2" %}
								<option value='{{OPT_ID}}'>{{OPT_VALUE}}</option>
							{% endif %}
						{% endfor %}
					</select>
				</div>
				<div class='form-group'>
					<input type='hidden' id='viewChecked' name='viewChecked' value='' />
				</div>
				<div class='form-group col-lg-12 col-md-12'>
					<label for='viewPermission' class='control-label'>View Permissions (who can see this view)</label>
					<select class='form-control' id='viewPermission' name='viewPermission'>
						<option value='public'>Public (Openly Available to all Users, Best for Non-Sensitive Data)</option>
						<option value='private' selected>Private (Available Only to You, and Groups of Users You Select)</option>
					</select>
				</div>
				<div id='viewGroupsBox' class='form-group col-lg-8 col-md-8'>
					<label for='viewGroups' class='control-label'>Permitted Groups (if you don't choose any, only <strong>YOU</strong> alone will have access to these files) [choose all that apply]</label>
					<select class='form-control' id='viewGroups' name='viewGroups' multiple>
						{% for groupID, groupInfo in GROUPS %}
							<option value='{{groupID}}'>{{groupInfo.group_name}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='addViewSubmit' type='submit'><strong>Create View</strong> <i class='fa fa-check'></i></button>
				</div>
			</form>
		</div>
	</div>
	<div id='messages' class='container-fluid marginTopSm'></div>
</div>

<input type='hidden' id='expIDs' value='{{ EXP_IDS }}' />
<input type='hidden' id='includeBG' value='{{ INCLUDE_BG }}' />

{% include 'blocks/ORCADataTableBlock.tpl' %}