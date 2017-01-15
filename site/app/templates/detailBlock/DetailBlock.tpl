<div id='orcaDetailBlockWrap' class='orcaDetailBlockWrap greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<h3>{{ TITLE }}</h3>
		<span id='orcaDetailBlockSubhead' class='subheadSmall'>{{ SUBHEAD | raw }}</span>
		<div class='orcaDetailBoxes marginTopSm'>
			{% for DETAIL in DETAILS %}
				<div class='orcaDetailBox'>
					{% if DETAIL.SIZE == "half" %}
						<div class='form-group col-lg-6 col-md-6 col-sm-12 col-xs-12'>
					{% elseif DETAIL.SIZE == "third" %}
						<div class='form-group col-lg-4 col-md-4 col-sm-12 col-xs-12'>
					{% elseif DETAIL.SIZE == "twothird" %}
						<div class='form-group col-lg-8 col-md-8 col-sm-12 col-xs-12'>
					{% else %}
						<div class='form-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>
					{% endif %}
						<label for='{{ DETAIL.NAME }}' class='orcaDetailBoxHeader control-label'>{{ DETAIL.HEADER }}</label>
						<input type='text' class='orcaDetailBoxBody form-control' id='{{ DETAIL.ID }}' name='{{ DETAIL.ID }}' value='{{ DETAIL.BODY }}' readonly />
					</div>
				</div>
			{% endfor %}
		</div>
	</div>
</div>