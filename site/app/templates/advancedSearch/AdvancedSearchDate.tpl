<div data-type='date' class='col-lg-4 col-md-4 col-sm-6 col-xs-12 orcaAdvancedField'>
	<div class='form-group clearfix'>
		<label for='column_{{ COLUMN }}' class='control-label'>{{ TITLE }}</label>
		<div class='clearfix'>
			<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>
				<select class='form-control dateEval' data-column='{{ COLUMN }}'>
					<option value='>='>> Greater Than</option>
					<option value='<='>< Less Than</option>
				</select>
			</div>
			<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>
				<input type='text' class='form-control dateVal' data-column='{{ COLUMN }}' placeholder='Date (YYYY-MM-DD)' />
			</div>
		</div>
	</div>
</div>