<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Manage Users <i class='fa fa-lg fa-users primaryIcon'></i> </h2>
		<div class='subheadLarge'>Use the following table to make adjustments to users in the system. This page is available only to powerusers and above, and you can only promote uers to your level or lower. Only an admin can create other admins, only a poweruser or admin can create powerusers etc. <strong>Note:</strong> Users will need to <strong><a href='{{WEB_URL}}/Home/Logout' title='Logout'>logout</a></strong> and then log back in again for changes to be reflected in their permissions.</div>
	</div>
</div>

<div id='managerUserWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='pull-right col-lg-3 col-md-4 col-sm-5 col-xs-6' style='padding-right: 0'>
			<div class='input-group marginBotSm marginTopSm'>
				<input type="text" name='manageUsersFilter' id='manageUsersFilter' class="form-control" placeholder="Enter Filter Term" value="" autofocus>
				<span class='input-group-btn'>
					<button class='btn btn-success' id='manageUsersFilterSubmit'>Filter <i class='fa fa-check'></i></button>
				</span>
			</div>
		</div>
		<h3>Current Users </h3>
		<span id='manageUsersFilterData' class='subheadSmall'></span>
		<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 paddingLeftNone paddingRightNone'>
			<table id='manageUsersTable' class='table table-striped table-bordered table-responsive table-condensed' width="100%"></table>
		</div>
		<input type='hidden' id='userCount' value='{{ USER_COUNT }}' />
	</div>
	<div id='messages' class='container-fluid'></div>
</div>

