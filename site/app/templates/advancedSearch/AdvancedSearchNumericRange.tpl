<div data-type='numericrange' class='col-lg-4 col-md-4 col-sm-6 col-xs-12 orcaAdvancedField'>
	<div class='form-group clearfix'>
		<label for='column_{{ COLUMN }}' class='control-label'>{{ TITLE }}</label>
		<div class='clearfix'>
			<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>
				<input type='text' class='form-control' data-column='{{ COLUMN }}' data-range='MIN' placeholder='Minimum Value' />
			</div>
			<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>
				<input type='text' class='form-control' data-column='{{ COLUMN }}' data-range='MAX' placeholder='Maximum Value' />
			</div>
		</div>
	</div>
</div>