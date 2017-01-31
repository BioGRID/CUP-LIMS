<select class='orcaSelect form-control input-sm {{ SELECT_CLASS }}'>
	{% for OPT_ID, OPT_INFO in OPTIONS %}
		<option value='{{ OPT_ID }}' {{ OPT_INFO.SELECTED }}>{{ OPT_INFO.NAME }}</option>
	{% endfor %}
</select>