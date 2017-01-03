<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Manage Permissions <i class='fa fa-lg fa-gavel primaryIcon'></i> </h2>
		<div class='subheadLarge'>Use the following table to make adjustments to the access permissions used by the system. This page is available only to admins. <strong>Note:</strong> Users will need to <strong><a href='{{WEB_URL}}/Home/Logout' title='Logout'>logout</a></strong> and then log back in again for changes to be reflected in their permissions.</div>
	</div>
</div>

<div id='addPermissionWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<h3>Add New Permission</h3>
		<span id='addNewPermissionSubhead' class='subheadSmall'>Use this form to add a new Permission Type to the Database</span>
	</div>
</div>

<div id='managerPermissionsWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='pull-right col-lg-3 col-md-4 col-sm-5 col-xs-6' style='padding-right: 0'>
			<div class='input-group marginBotSm marginTopSm'>
				<input type="text" name='managePermissionsFilter' id='managePermissionsFilter' class="form-control" placeholder="Enter Filter Term" value="" autofocus>
				<span class='input-group-btn'>
					<button class='btn btn-success' id='managePermissionsFilterSubmit'>Filter <i class='fa fa-check'></i></button>
				</span>
			</div>
		</div>
		<h3>Current Permission Settings</h3>
		<span id='managePermissionsFilterData' class='subheadSmall'></span>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 paddingLeftNone paddingRightNone'>
			<table id='managePermissionsTable' class='table table-striped table-bordered table-responsive table-condensed' width="100%"></table>
		</div>
		<input type='hidden' id='permissionsCount' value='{{ PERMISSION_COUNT }}' />
	</div>
</div>