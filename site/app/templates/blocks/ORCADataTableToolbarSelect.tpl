<div class='{{ SELECT_CLASS }}'>
<select class='orcaToolbarSelect form-control input-sm'>
	{% for OPT_ID, OPT_INFO in OPTIONS %}
		{% if OPT_ID != "ALL" %}
			<option value='{{ OPT_ID }}'>[{{ OPT_ID }}] {{ OPT_INFO }}</option>
		{% else %}
			<option value='{{ OPT_ID }}'>{{ OPT_INFO }}</option>
		{% endif %}
	{% endfor %}
</select>
</div>