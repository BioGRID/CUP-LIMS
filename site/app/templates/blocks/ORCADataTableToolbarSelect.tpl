<div class='{{ SELECT_CLASS }}'>
<select class='orcaToolbarSelect form-control input-sm'>
	{% for OPT_ID, OPT_INFO in OPTIONS %}
		<option value='{{ OPT_ID }}' {{ OPT_INFO.SELECTED }}>{{ OPT_INFO.NAME }}</option>
	{% endfor %}
</select>
</div>