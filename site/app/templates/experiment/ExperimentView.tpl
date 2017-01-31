<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Experiment (#{{ EXPERIMENT_ID }}) : {{ EXPERIMENT_NAME }} <i class='fa fa-lg fa-flask primaryIcon'></i></h2>
		<div class='subheadLarge'>The following is a summary of loaded experimental information for experiment (<span class='text-success'>#{{ EXPERIMENT_ID }}</span>): <strong><span class='text-success'>{{ EXPERIMENT_NAME }}</span></strong>.</div>
	</div>
</div>

{% include 'detailBlock/DetailBlock.tpl' %}

{% if SHOW_FILES %}
	{% include 'blocks/ORCADataTableBlock.tpl' %}

	<input type='hidden' id='ids' value='{{ IDS }}' />
	<input type='hidden' id='includeBG' value='{{ INCLUDE_BG }}' />
	<input type='hidden' id='type' value='{{ TYPE }}' />
{% endif %}