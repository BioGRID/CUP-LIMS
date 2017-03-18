<div class='primaryContent'>
	<div class='container-fluid'>
		{% if VIEW_CREATE_VALID %}
			<div class='pull-right paddingTopXs'>
				<a class='btn btn-success btn-lg' href='{{ WEB_URL }}/Files' title='Create New View'>Create New View <i class='fa fa-bar-chart fa-lg'></i></a>
			</div>
		{% endif %}
		<h2>Views <i class='fa fa-lg fa-bar-chart primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a listing of custom generated views. Click on the view name below to visualize the created view.</div>
	</div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}

