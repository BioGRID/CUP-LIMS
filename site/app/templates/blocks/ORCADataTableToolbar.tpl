<div class='orcaDataTableToolbar'>
	{% if not HIDE_CHECK_ALL %}
		<button type='button' data-status='check' class='orcaDataTableCheckAll btn btn-primary btn-sm'><i class='fa fa-check fa-lg'></i></button>
	{% endif %}
	{{ BUTTONS|raw }}
</div>