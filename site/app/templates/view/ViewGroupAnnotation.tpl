<div class='orcaGroupAnnotation'>
	{% for ANN_NAME, ANN_VAL in ANNOTATION %}
		<div class='orcaGroupAnnotationRow'><strong>{{ ANN_NAME }}</strong>: {{ ANN_VAL }}</div>
	{% endfor %}
	
	{% if LINKS %}
		<div class='orcaGroupAnnotationRow'><strong>Links</strong> :
			{% for LINK_NAME, LINK_URL in LINKS %}
				[<a href='{{ LINK_URL|raw }}' target='_BLANK' title='VIEW IN {{ LINK_NAME }}'>{{ LINK_NAME }}</a>] 
			{% endfor %}
		</div>
	{% endif %}
</div>