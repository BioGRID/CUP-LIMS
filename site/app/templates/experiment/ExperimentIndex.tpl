<div class='primaryContent'>
	<div class='container-fluid'>
		{% if UPLOAD_VALID %}
			<div class='pull-right paddingTopXs'>
				<a class='btn btn-success btn-lg' href='{{ WEB_URL }}/Upload' title='Upload New Experiment'>Upload New Experiment <i class='fa fa-cloud-upload fa-lg'></i></a>
			</div>
		{% endif %}
		<h2>Experiments <i class='fa fa-lg fa-flask primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a listing of uploaded experiments currently residing in the {{ WEB_NAME_ABBR }} database.</div>
	</div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}

