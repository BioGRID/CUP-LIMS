<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Create View <i class='fa fa-lg fa-bar-chart primaryIcon'></i> </h2>
		<div class='subheadLarge'>Fill in the following form with details for your view, and select files to include from the table listing below.</div>
	</div>
</div>

<div id='addViewWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<h3>Add New View</h3>
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
							<option value='{{OPT_ID}}'>{{OPT_VALUE}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='form-group col-lg-6 col-md-6'>
					<label for='viewValue' class='control-label'>View Value</label>
					<select class='form-control' id='viewValue' name='viewValue'>
						{% for OPT_ID,OPT_VALUE in VIEW_VALUES %}
							<option value='{{OPT_ID}}'>{{OPT_VALUE}}</option>
						{% endfor %}
					</select>
				</div>
				<div class='form-group'>
					<input type='hidden' id='viewChecked' name='viewChecked' value='' />
				</div>
				<div class='col-lg-12 col-md-12'>
					<button class='btn btn-success btn-lg' id='addViewSubmit' type='submit'><strong>Create View</strong> <i class='fa fa-check'></i></button>
				</div>
			</form>
		</div>
	</div>
	<div id='messages' class='container-fluid marginTopSm'></div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}